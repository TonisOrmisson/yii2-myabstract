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
            'created_at' => Schema::TYPE_DATETIME . '(6) NOT NULL',
            'updated_at' => Schema::TYPE_DATETIME . '(6) NOT NULL',
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

        if (str_ends_with($columnName, '_by') && $column->type !== Schema::TYPE_INTEGER) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }

        if (str_ends_with($columnName, '_at') && !in_array($column->type, [Schema::TYPE_DATETIME, Schema::TYPE_TIMESTAMP], true)) {
            throw new InvalidConfigException("Conflicting audit column {$tableName}.{$columnName}");
        }
    }

    private function ensureIndex(string $tableName, string $columnName): void
    {
        $indexName = $this->indexName($tableName, $columnName);
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        $this->db->createCommand()->createIndex($indexName, $tableName, $columnName)->execute();
    }

    private function indexName(string $tableName, string $columnName): string
    {
        return 'idx_' . $tableName . '_' . $columnName;
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        if ($this->db->driverName === 'sqlite') {
            $rows = $this->db->createCommand("PRAGMA index_list('{$tableName}')")->queryAll();

            return in_array($indexName, array_column($rows, 'name'), true);
        }

        if ($this->db->driverName === 'mysql') {
            $rows = $this->db
                ->createCommand('SHOW INDEX FROM ' . $this->db->quoteTableName($tableName) . ' WHERE Key_name = :indexName')
                ->bindValue(':indexName', $indexName)
                ->queryAll();

            return $rows !== [];
        }

        throw new InvalidConfigException("AuditSchemaHelper index inspection does not support {$this->db->driverName}.");
    }
}
