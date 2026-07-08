<?php

declare(strict_types=1);

namespace andmemasin\myabstract\tests\unit;

use andmemasin\myabstract\schema\AuditSchemaHelper;
use Codeception\Test\Unit;
use Yii;
use yii\db\Schema;

final class AuditSchemaHelperTest extends Unit
{
    private const DOMAIN_TABLE = 'audit_schema_domain';
    private const SKIPPED_TABLE = 'audit_schema_skipped';
    private const TEMP_TABLE = 'temp_audit_schema';
    private const DEFAULT_AUDIT_TIMESTAMP = '1970-01-01 00:00:00.000000';

    protected function _before(): void
    {
        $this->dropTables();
        Yii::$app->db->createCommand()->createTable(self::DOMAIN_TABLE, [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
        ])->execute();
        Yii::$app->db->createCommand()->createTable(self::SKIPPED_TABLE, [
            'id' => Schema::TYPE_PK,
        ])->execute();
        Yii::$app->db->createCommand()->createTable(self::TEMP_TABLE, [
            'id' => Schema::TYPE_PK,
        ])->execute();
        Yii::$app->db->createCommand()->createTable('user', [
            'id' => Schema::TYPE_PK,
        ])->execute();
    }

    protected function _after(): void
    {
        $this->dropTables();
    }

    public function testEnsureAddsOnlyCurrentAuditColumns(): void
    {
        $helper = new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]);

        $helper->ensure();

        $columns = Yii::$app->db->schema->getTableSchema(self::DOMAIN_TABLE, true)->columnNames;
        sort($columns);

        $this->assertSame([
            'created_at',
            'created_by',
            'deleted_at',
            'deleted_by',
            'id',
            'name',
            'updated_at',
            'updated_by',
        ], $columns);
        foreach (AuditSchemaHelper::legacyColumns() as $legacyColumn) {
            $this->assertFalse(Yii::$app->db->schema->getTableSchema(self::DOMAIN_TABLE, true)->getColumn($legacyColumn) !== null);
        }

        $indexNames = $this->indexNames(self::DOMAIN_TABLE);
        foreach (AuditSchemaHelper::indexedColumns() as $indexedColumn) {
            $this->assertContains('idx_' . self::DOMAIN_TABLE . '_' . $indexedColumn, $indexNames);
        }
    }

    public function testEnsureAddsRequiredDefaultsToPopulatedTable(): void
    {
        Yii::$app->db->createCommand()->insert(self::DOMAIN_TABLE, ['name' => 'existing-row'])->execute();

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();

        $row = Yii::$app->db->createCommand('SELECT * FROM ' . self::DOMAIN_TABLE)->queryOne();

        $this->assertSame(1, (int) $row['created_by']);
        $this->assertSame(1, (int) $row['updated_by']);
        $this->assertSame(self::DEFAULT_AUDIT_TIMESTAMP, $row['created_at']);
        $this->assertSame(self::DEFAULT_AUDIT_TIMESTAMP, $row['updated_at']);
        $this->assertNull($row['deleted_by']);
        $this->assertNull($row['deleted_at']);
    }

    public function testEnsureSkipsDefaultAndConfiguredTables(): void
    {
        $helper = new AuditSchemaHelper(Yii::$app->db, ['user', self::SKIPPED_TABLE], [self::SKIPPED_TABLE]);

        $helper->ensure();

        $this->assertSame(['id'], Yii::$app->db->schema->getTableSchema('user', true)->columnNames);
        $this->assertSame(['id'], Yii::$app->db->schema->getTableSchema(self::SKIPPED_TABLE, true)->columnNames);
    }

    public function testEnsureSkipsTempTables(): void
    {
        $helper = new AuditSchemaHelper(Yii::$app->db, [self::TEMP_TABLE]);

        $helper->ensure();

        $this->assertSame(['id'], Yii::$app->db->schema->getTableSchema(self::TEMP_TABLE, true)->columnNames);
        $this->assertSame([], $this->indexNames(self::TEMP_TABLE));
    }

    public function testEnsureIsIdempotent(): void
    {
        $helper = new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]);

        $helper->ensure();
        $helper->ensure();

        $this->assertNotNull(Yii::$app->db->schema->getTableSchema(self::DOMAIN_TABLE, true)->getColumn('deleted_at'));
    }

    public function testEnsureFailsOnConflictingColumnDefinition(): void
    {
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'deleted_at', Schema::TYPE_INTEGER . ' NULL')->execute();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('Conflicting audit column audit_schema_domain.deleted_at');

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();
    }

    public function testEnsureFailsOnNullableRequiredAuditColumn(): void
    {
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'created_by', Schema::TYPE_INTEGER . ' NULL DEFAULT 1')->execute();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('Conflicting audit column audit_schema_domain.created_by');

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();
    }

    public function testEnsureFailsOnWrongRequiredDefault(): void
    {
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'updated_by', Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 2')->execute();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('Conflicting audit column audit_schema_domain.updated_by');

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();
    }

    public function testEnsureAcceptsCurrentTimestampDefaultForRequiredDatetime(): void
    {
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'created_at', Schema::TYPE_DATETIME . ' NOT NULL DEFAULT CURRENT_TIMESTAMP')->execute();

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();

        $this->assertNotNull(Yii::$app->db->schema->getTableSchema(self::DOMAIN_TABLE, true)->getColumn('created_at'));
    }

    public function testEnsureFailsOnWrongDatetimePrecisionWhenIntrospectable(): void
    {
        $definition = Yii::$app->db->driverName === 'mysql'
            ? Schema::TYPE_DATETIME . " NOT NULL DEFAULT '" . self::DEFAULT_AUDIT_TIMESTAMP . "'"
            : Schema::TYPE_TIMESTAMP . " NOT NULL DEFAULT '" . self::DEFAULT_AUDIT_TIMESTAMP . "'";
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'created_at', $definition)->execute();

        if (Yii::$app->db->driverName !== 'mysql') {
            $column = Yii::$app->db->schema->getTableSchema(self::DOMAIN_TABLE, true)->getColumn('created_at');
            if ($column === null || $column->type !== Schema::TYPE_TIMESTAMP) {
                $this->markTestSkipped('Driver does not expose the wrong datetime type for this check.');
            }
        }

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('Conflicting audit column audit_schema_domain.created_at');

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();
    }

    public function testEnsureFailsOnConflictingNamedIndexColumns(): void
    {
        Yii::$app->db->createCommand()->createIndex(
            'idx_' . self::DOMAIN_TABLE . '_deleted_at',
            self::DOMAIN_TABLE,
            'name',
        )->execute();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('Conflicting audit index idx_audit_schema_domain_deleted_at on audit_schema_domain');

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();
    }

    public function testEnsureDoesNotDuplicateCorrectCustomNamedIndex(): void
    {
        Yii::$app->db->createCommand()->addColumn(self::DOMAIN_TABLE, 'deleted_at', Schema::TYPE_DATETIME . '(6) NULL')->execute();
        Yii::$app->db->createCommand()->createIndex('custom_deleted_at_idx', self::DOMAIN_TABLE, 'deleted_at')->execute();

        (new AuditSchemaHelper(Yii::$app->db, [self::DOMAIN_TABLE]))->ensure();

        $deletedAtIndexes = $this->indexNamesByColumn(self::DOMAIN_TABLE)['deleted_at'] ?? [];
        sort($deletedAtIndexes);

        $this->assertSame(['custom_deleted_at_idx'], $deletedAtIndexes);
        $this->assertNotContains('idx_' . self::DOMAIN_TABLE . '_deleted_at', $this->indexNames(self::DOMAIN_TABLE));
    }

    private function dropTables(): void
    {
        foreach ([self::DOMAIN_TABLE, self::SKIPPED_TABLE, self::TEMP_TABLE, 'user'] as $tableName) {
            if (Yii::$app->db->schema->getTableSchema($tableName, true) !== null) {
                Yii::$app->db->createCommand()->dropTable($tableName)->execute();
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function indexNames(string $tableName): array
    {
        $rows = Yii::$app->db->createCommand("PRAGMA index_list('{$tableName}')")->queryAll();

        return array_column($rows, 'name');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function indexNamesByColumn(string $tableName): array
    {
        $indexesByColumn = [];
        foreach ($this->indexNames($tableName) as $indexName) {
            $rows = Yii::$app->db->createCommand("PRAGMA index_info('{$indexName}')")->queryAll();
            if (count($rows) !== 1) {
                continue;
            }

            $columnName = $rows[0]['name'] ?? null;
            if (!is_string($columnName)) {
                continue;
            }

            $indexesByColumn[$columnName] ??= [];
            $indexesByColumn[$columnName][] = $indexName;
        }

        return $indexesByColumn;
    }
}
