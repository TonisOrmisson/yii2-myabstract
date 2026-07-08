<?php

declare(strict_types=1);

namespace andmemasin\myabstract\schema;

use yii\base\InvalidConfigException;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;

final class AuditSchemaHelper
{
    private const DEFAULT_AUDIT_TIMESTAMP = '1970-01-01 00:00:00.000000';

    public const DEFAULT_SKIP_TABLES = [
        'migration',
        'user',
        'auth_assignment',
        'auth_item',
        'auth_item_child',
        'auth_rule',
        'token',
        'session',
        'social_account',
        'profile',
    ];

    /**
     * @param array<int, string> $tables
     * @param array<int, string> $skipTables
     */
    public function __construct(
        private readonly Connection $db,
        private readonly array $tables,
        array $skipTables = self::DEFAULT_SKIP_TABLES,
    ) {
        if ($this->tables === []) {
            throw new InvalidConfigException('AuditSchemaHelper requires an explicit table list.');
        }

        $this->skipTables = array_values(array_unique(array_merge(self::DEFAULT_SKIP_TABLES, $skipTables)));
    }

    /**
     * @var array<int, string>
     */
    private array $skipTables;

    public function ensure(): void
    {
        foreach ($this->tables as $tableName) {
            if ($this->shouldSkip($tableName)) {
                continue;
            }

            $schema = $this->tableSchema($tableName);
            foreach (self::currentColumns() as $columnName => $definition) {
                $column = $schema->getColumn($columnName);
                if ($column === null) {
                    $this->db->createCommand()->addColumn($tableName, $columnName, $definition)->execute();
                    $this->db->schema->refreshTableSchema($tableName);
                    $schema = $this->tableSchema($tableName);
                    $column = $schema->getColumn($columnName);
                }

                $this->assertColumnCompatible($tableName, $columnName, $column);
            }

            foreach (self::indexedColumns() as $columnName) {
                $this->ensureIndex($tableName, $columnName);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public static function currentColumns(): array
    {
        return [
            'created_by' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
            'updated_by' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
            'deleted_by' => Schema::TYPE_INTEGER . ' NULL',
            'created_at' => Schema::TYPE_DATETIME . "(6) NOT NULL DEFAULT '" . self::DEFAULT_AUDIT_TIMESTAMP . "'",
            'updated_at' => Schema::TYPE_DATETIME . "(6) NOT NULL DEFAULT '" . self::DEFAULT_AUDIT_TIMESTAMP . "'",
            'deleted_at' => Schema::TYPE_DATETIME . '(6) NULL',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function indexedColumns(): array
    {
        return [
            'deleted_at',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function legacyColumns(): array
    {
        return [
            'user_created',
            'user_updated',
            'user_closed',
            'time_created',
            'time_updated',
            'time_closed',
        ];
    }

    private function shouldSkip(string $tableName): bool
    {
        return in_array($tableName, $this->skipTables, true) || str_starts_with($tableName, 'temp_');
    }

    private function tableSchema(string $tableName): TableSchema
    {
        $schema = $this->db->schema->getTableSchema($tableName, true);
        if (!$schema instanceof TableSchema) {
            throw new InvalidConfigException("Table {$tableName} does not exist.");
        }

        return $schema;
    }

    private function assertColumnCompatible(string $tableName, string $columnName, ?ColumnSchema $column): void
    {
        if (!$column instanceof ColumnSchema) {
            throw new InvalidConfigException("Audit column {$tableName}.{$columnName} was not created.");
        }

        $expected = $this->expectedColumnConfig($columnName);

        if ($column->type !== $expected['type']) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }

        if ($column->allowNull !== $expected['allowNull']) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }

        if (array_key_exists('defaultValue', $expected) && !$this->defaultValuesMatch($column->defaultValue, $expected['defaultValue'])) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }

        if (str_ends_with($columnName, '_at') && !$this->hasExpectedDateTimePrecision($tableName, $columnName, $column)) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }
    }

    private function ensureIndex(string $tableName, string $columnName): void
    {
        $indexName = $this->indexName($tableName, $columnName);
        $indexes = $this->indexes($tableName);
        foreach ($indexes as $index) {
            if ($index['columns'] === [$columnName]) {
                return;
            }
        }

        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                throw new InvalidConfigException("Conflicting audit index {$indexName} on {$tableName}");
            }
        }

        $this->db->createCommand()->createIndex($indexName, $tableName, $columnName)->execute();
    }

    private function indexName(string $tableName, string $columnName): string
    {
        return 'idx_' . $tableName . '_' . $columnName;
    }

    /**
     * @return array{type: string, allowNull: bool, defaultValue?: int|string|null}
     */
    private function expectedColumnConfig(string $columnName): array
    {
        return match ($columnName) {
            'created_by', 'updated_by' => [
                'type' => Schema::TYPE_INTEGER,
                'allowNull' => false,
                'defaultValue' => 1,
            ],
            'deleted_by' => [
                'type' => Schema::TYPE_INTEGER,
                'allowNull' => true,
            ],
            'created_at', 'updated_at' => [
                'type' => Schema::TYPE_DATETIME,
                'allowNull' => false,
                'defaultValue' => self::DEFAULT_AUDIT_TIMESTAMP,
            ],
            'deleted_at' => [
                'type' => Schema::TYPE_DATETIME,
                'allowNull' => true,
            ],
            default => throw new InvalidConfigException("Unknown audit column {$columnName}."),
        };
    }

    private function defaultValuesMatch(mixed $actual, mixed $expected): bool
    {
        if ($actual === null || $expected === null) {
            return $actual === $expected;
        }

        if (!is_scalar($actual) || !is_scalar($expected)) {
            return false;
        }

        $normalize = static function (string|int|float|bool $value): string {
            if (is_string($value)) {
                return trim($value, '\'"');
            }

            return (string) $value;
        };

        return $normalize($actual) === $normalize($expected);
    }

    private function hasExpectedDateTimePrecision(string $tableName, string $columnName, ColumnSchema $column): bool
    {
        $dbType = $this->timeColumnDbType($tableName, $columnName, $column);
        if ($dbType === null) {
            return true;
        }

        if ($this->db->driverName === 'sqlite' && !str_contains($dbType, '(')) {
            return true;
        }

        return preg_match('/^datetime\s*\(6\)$/', $dbType) === 1;
    }

    private function timeColumnDbType(string $tableName, string $columnName, ColumnSchema $column): ?string
    {
        if ($this->db->driverName === 'sqlite') {
            $rows = $this->db->createCommand("PRAGMA table_info('{$tableName}')")->queryAll();
            foreach ($rows as $row) {
                if (($row['name'] ?? null) === $columnName && isset($row['type']) && is_string($row['type'])) {
                    return strtolower($row['type']);
                }
            }
        }

        if ($this->db->driverName === 'mysql') {
            $row = $this->db
                ->createCommand('SHOW FULL COLUMNS FROM ' . $this->db->quoteTableName($tableName) . ' WHERE Field = :columnName')
                ->bindValue(':columnName', $columnName)
                ->queryOne();

            if (is_array($row) && isset($row['Type']) && is_string($row['Type'])) {
                return strtolower($row['Type']);
            }
        }

        if ($column->dbType !== '') {
            return strtolower($column->dbType);
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, columns: array<int, string>}>
     */
    private function indexes(string $tableName): array
    {
        return match ($this->db->driverName) {
            'sqlite' => $this->sqliteIndexes($tableName),
            'mysql' => $this->mysqlIndexes($tableName),
            default => throw new InvalidConfigException("AuditSchemaHelper index inspection does not support {$this->db->driverName}."),
        };
    }

    /**
     * @return array<int, array{name: string, columns: array<int, string>}>
     */
    private function sqliteIndexes(string $tableName): array
    {
        $indexes = [];
        $rows = $this->db->createCommand("PRAGMA index_list('{$tableName}')")->queryAll();
        foreach ($rows as $row) {
            $indexName = $row['name'] ?? null;
            if (!is_string($indexName)) {
                continue;
            }

            $indexRows = $this->db->createCommand("PRAGMA index_info('{$indexName}')")->queryAll();
            $columns = [];
            foreach ($indexRows as $indexRow) {
                $columnName = $indexRow['name'] ?? null;
                if (is_string($columnName)) {
                    $columns[] = $columnName;
                }
            }

            $indexes[] = ['name' => $indexName, 'columns' => $columns];
        }

        return $indexes;
    }

    /**
     * @return array<int, array{name: string, columns: array<int, string>}>
     */
    private function mysqlIndexes(string $tableName): array
    {
        $grouped = [];
        $rows = $this->db->createCommand('SHOW INDEX FROM ' . $this->db->quoteTableName($tableName))->queryAll();
        foreach ($rows as $row) {
            $indexName = $row['Key_name'] ?? null;
            $columnName = $row['Column_name'] ?? null;
            $sequence = $row['Seq_in_index'] ?? null;
            if (!is_string($indexName) || !is_string($columnName) || !is_numeric($sequence)) {
                continue;
            }

            $grouped[$indexName][(int) $sequence] = $columnName;
        }

        $indexes = [];
        foreach ($grouped as $indexName => $columnsBySequence) {
            ksort($columnsBySequence);
            $indexes[] = ['name' => $indexName, 'columns' => array_values($columnsBySequence)];
        }

        return $indexes;
    }
}
