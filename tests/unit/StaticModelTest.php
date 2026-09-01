<?php
namespace andmemasin\myabstract;

use Codeception\Stub;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;

class StaticModelTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var StaticModel */
    private $model;

    protected function _before()
    {
        $this->model = new StaticModel();
    }

    protected function _after()
    {
    }

    public function testGetModels()
    {
        $this->assertEquals([], $this->model->getModelAttributes());
    }

    public function testGetByIdNoModels()
    {
        $this->assertNull($this->model::getById("there-is-nothing"));
    }

    public function testGetByKeyNoModels()
    {
        $this->assertNull($this->model::getByKey("there-is-nothing"));
    }

    public function testGetByKeyUsesLastDuplicateValue(): void
    {
        $model = IndexedStaticModel::getByKey('duplicate');

        $this->assertInstanceOf(IndexedStaticModel::class, $model);
        $this->assertSame('last', $model->value);
    }

    public function testYiiArrayIndexPreservesIndexByColumnBehavior(): void
    {
        $model = new DynamicModel(['key' => 'duplicate', 'value' => 'model']);
        $array = ['key' => 7, 'value' => 'array'];

        $this->assertSame(
            [7 => $array, 'duplicate' => $model],
            ArrayHelper::index([$array, ['key' => 'duplicate', 'value' => 'first'], $model], 'key')
        );
        $this->assertSame([], ArrayHelper::index([], 'key'));
    }
}

final class IndexedStaticModel extends StaticModel
{
    public string $key = '';
    public string $value = '';

    public function getModelAttributes(): array
    {
        return [
            ['key' => 'duplicate', 'value' => 'first'],
            ['key' => 'duplicate', 'value' => 'last'],
        ];
    }
}