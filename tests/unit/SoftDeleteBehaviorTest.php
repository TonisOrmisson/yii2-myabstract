<?php

namespace andmemasin\myabstract;

use Yii;
use yii\db\Schema;

final class SoftDeleteBehaviorTest extends \Codeception\Test\Unit
{
    protected function _before(): void
    {
        $this->dropTables();
        $this->createStandardTable();
        $this->createLegacyTable();
    }

    protected function _after(): void
    {
        $this->dropTables();
    }

    public function testNewRecordUsesStandardColumns(): void
    {
        $record = new StandardSoftDeleteRecord(['name' => 'active']);

        $this->assertTrue($record->save());
        $this->assertNotEmpty($record->created_at);
        $this->assertNotEmpty($record->updated_at);
        $this->assertNull($record->deleted_at);
        $this->assertSame(1, $record->created_by);
        $this->assertSame(1, $record->updated_by);
        $this->assertNull($record->deleted_by);
    }

    public function testFindExcludesDeletedRowsByDeletedAt(): void
    {
        $active = new StandardSoftDeleteRecord(['name' => 'active']);
        $deleted = new StandardSoftDeleteRecord(['name' => 'deleted']);

        $this->assertTrue($active->save());
        $this->assertTrue($deleted->save());
        $this->assertSame(1, $deleted->delete());

        $names = StandardSoftDeleteRecord::find()->select('name')->column();

        $this->assertSame(['active'], $names);
        $this->assertCount(2, StandardSoftDeleteRecord::findWithDeleted()->all());
    }

    public function testBulkDeleteUsesDeletedAt(): void
    {
        $record = new StandardSoftDeleteRecord(['name' => 'bulk']);

        $this->assertTrue($record->save());
        StandardSoftDeleteRecord::bulkDelete(['name' => 'bulk']);

        $reloaded = StandardSoftDeleteRecord::findWithDeleted()->where(['name' => 'bulk'])->one();

        $this->assertNotNull($reloaded);
        $this->assertNotNull($reloaded->deleted_at);
        $this->assertSame(1, $reloaded->deleted_by);
        $this->assertNull(StandardSoftDeleteRecord::find()->andWhere(['name' => 'bulk'])->one());
    }

    public function testExplicitLegacyOverridesStillWork(): void
    {
        $record = new LegacySoftDeleteRecord(['name' => 'legacy']);

        $this->assertTrue($record->save());
        $this->assertNotEmpty($record->time_created);
        $this->assertNotEmpty($record->time_updated);
        $this->assertNull($record->time_closed);
        $this->assertSame(1, $record->user_created);
        $this->assertSame(1, $record->user_updated);
        $this->assertNull($record->user_closed);
    }

    private function createStandardTable(): void
    {
        Yii::$app->db->createCommand()->createTable(StandardSoftDeleteRecord::tableName(), [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_DATETIME . '(6) NOT NULL',
            'updated_at' => Schema::TYPE_DATETIME . '(6) NOT NULL',
            'deleted_at' => Schema::TYPE_DATETIME . '(6) NULL',
            'created_by' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_by' => Schema::TYPE_INTEGER . ' NOT NULL',
            'deleted_by' => Schema::TYPE_INTEGER . ' NULL',
        ])->execute();
    }

    private function createLegacyTable(): void
    {
        Yii::$app->db->createCommand()->createTable(LegacySoftDeleteRecord::tableName(), [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'time_created' => Schema::TYPE_DATETIME . '(6) NOT NULL',
            'time_updated' => Schema::TYPE_DATETIME . '(6) NOT NULL',
            'time_closed' => Schema::TYPE_DATETIME . '(6) NULL',
            'user_created' => Schema::TYPE_INTEGER . ' NOT NULL',
            'user_updated' => Schema::TYPE_INTEGER . ' NOT NULL',
            'user_closed' => Schema::TYPE_INTEGER . ' NULL',
        ])->execute();
    }

    private function dropTables(): void
    {
        foreach ([StandardSoftDeleteRecord::tableName(), LegacySoftDeleteRecord::tableName()] as $tableName) {
            if (Yii::$app->db->schema->getTableSchema($tableName, true) !== null) {
                Yii::$app->db->createCommand()->dropTable($tableName)->execute();
            }
        }
    }
}

final class StandardSoftDeleteRecord extends MyActiveRecord
{
    public static function tableName(): string
    {
        return 'standard_soft_delete_record';
    }
}

final class LegacySoftDeleteRecord extends MyActiveRecord
{
    public string $userCreatedCol = 'user_created';
    public string $userUpdatedCol = 'user_updated';
    public string $userClosedCol = 'user_closed';
    public string $timeCreatedCol = 'time_created';
    public string $timeUpdatedCol = 'time_updated';
    public string $timeClosedCol = 'time_closed';

    public static function tableName(): string
    {
        return 'legacy_soft_delete_record';
    }
}
