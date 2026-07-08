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

    public function testEnsureSkipsDefaultAndConfiguredTables(): void
    {
        $helper = new AuditSchemaHelper(Yii::$app->db, ['user', self::SKIPPED_TABLE], [self::SKIPPED_TABLE]);

        $helper->ensure();

        $this->assertSame(['id'], Yii::$app->db->schema->getTableSchema('user', true)->columnNames);
        $this->assertSame(['id'], Yii::$app->db->schema->getTableSchema(self::SKIPPED_TABLE, true)->columnNames);
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

    private function dropTables(): void
    {
        foreach ([self::DOMAIN_TABLE, self::SKIPPED_TABLE, 'user'] as $tableName) {
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
}
