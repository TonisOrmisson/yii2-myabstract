<?php

namespace andmemasin\myabstract;

use Yii;
use yii\caching\ArrayCache;
use yii\db\ActiveRecord;

final class MyActiveQueryCacheTest extends \Codeception\Test\Unit
{
    protected function _before(): void
    {
        Yii::$app->set('cache', new ArrayCache());
        Yii::$app->setModule('myabstract', new Module('myabstract', null, [
            'useCache' => true,
            'checkQueryClassOverridden' => false,
        ]));
        Yii::$app->db->createCommand()->createTable(ShimRecord::tableName(), [
            'shim_record_id' => 'pk',
            'name' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->insert(ShimRecord::tableName(), ['name' => 'a'])->execute();
    }

    protected function _after(): void
    {
        Yii::$app->db->createCommand()->dropTable(ShimRecord::tableName())->execute();
    }

    public function testAllSkipsCacheForRecordWithoutInterface(): void
    {
        $this->assertCount(1, $this->query()->all());
    }

    public function testOneByPrimaryKeySkipsCacheForRecordWithoutInterface(): void
    {
        $record = $this->query()->where(['shim_record_id' => 1])->one();

        $this->assertInstanceOf(ShimRecord::class, $record);
    }

    private function query(): MyActiveQuery
    {
        return new MyActiveQuery(ShimRecord::class);
    }
}

/** A plain record that only has primaryKeySingle(), like Language 4. */
final class ShimRecord extends ActiveRecord
{
    public static function primaryKeySingle(): string
    {
        return 'shim_record_id';
    }

    public static function tableName(): string
    {
        return 'shim_record';
    }
}
