<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\exceptions\MyAbstractException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

final class MyActiveQueryJunctionTest extends \Codeception\Test\Unit
{
    public function testViaTableFailsLoudly(): void
    {
        $query = $this->relationQuery();

        $this->expectException(MyAbstractException::class);
        $query->viaTable('{{plain_junction}}', ['owner_id' => 'id']);
    }

    public function testUsingRejectsNonActiveRecordClass(): void
    {
        $query = $this->relationQuery();

        $this->expectException(MyAbstractException::class);
        $query->using(\stdClass::class, ['owner_id' => 'id']);
    }

    public function testUsingDerivesPlainJunctionTable(): void
    {
        $query = $this->relationQuery()->using(
            PlainJunctionRecord::class,
            ['owner_id' => 'id'],
        );

        $this->assertInstanceOf(ActiveQuery::class, $query->via);
        $this->assertSame([PlainJunctionRecord::tableName()], array_values($query->via->from));
        $this->assertNull($query->via->where);
    }

    public function testUsingAppliesLogicalDeleteCondition(): void
    {
        $query = $this->relationQuery()->using(
            SoftDeleteJunctionRecord::class,
            ['owner_id' => 'id'],
        );

        $this->assertSame(
            ['is', SoftDeleteJunctionRecord::tableName() . '.deleted_at', null],
            $query->via->where,
        );
    }

    public function testUsingSkipsDisabledLogicalDelete(): void
    {
        $query = $this->relationQuery()->using(
            PhysicalDeleteJunctionRecord::class,
            ['owner_id' => 'id'],
        );

        $this->assertNull($query->via->where);
    }

    public function testUsingRunsCallbackAfterLogicalDeleteCondition(): void
    {
        $query = $this->relationQuery()->using(
            SoftDeleteJunctionRecord::class,
            ['owner_id' => 'id'],
            static function (Query $junctionQuery): void {
                $junctionQuery->andWhere(['approved' => 1]);
            },
        );

        $this->assertSame(
            [
                'and',
                ['is', SoftDeleteJunctionRecord::tableName() . '.deleted_at', null],
                ['approved' => 1],
            ],
            $query->via->where,
        );
    }

    private function relationQuery(): MyActiveQuery
    {
        return new MyActiveQuery(RelatedRecord::class, [
            'primaryModel' => new OwnerRecord(),
            'link' => ['id' => 'related_id'],
            'multiple' => true,
        ]);
    }
}

final class OwnerRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{owner}}';
    }
}

final class RelatedRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{related}}';
    }
}

final class PlainJunctionRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{plain_junction}}';
    }
}

final class SoftDeleteJunctionRecord extends MyActiveRecord
{
    public static function tableName(): string
    {
        return '{{soft_delete_junction}}';
    }
}

final class PhysicalDeleteJunctionRecord extends MyActiveRecord
{
    public bool $is_logicDelete = false;

    public static function tableName(): string
    {
        return '{{physical_delete_junction}}';
    }
}
