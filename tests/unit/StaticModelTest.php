<?php
namespace andmemasin\myabstract;

use Codeception\Stub;
use yii\base\InvalidArgumentException;

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
        IndexedStaticModel::$attributes = [
            ['key' => 'duplicate', 'value' => 'first'],
            ['key' => 'duplicate', 'value' => 'last'],
        ];

        $model = IndexedStaticModel::getByKey('duplicate');

        $this->assertInstanceOf(IndexedStaticModel::class, $model);
        $this->assertSame('last', $model->value);
    }

    public function testGetByKeyRejectsInvalidIndexValues(): void
    {
        $exceptions = [];
        foreach ([[[]], [['key' => null]], [['key' => false]]] as $attributes) {
            IndexedStaticModel::$attributes = $attributes;
            try {
                IndexedStaticModel::getByKey('duplicate');
            } catch (InvalidArgumentException $exception) {
                $exceptions[] = $exception;
            }
        }

        $this->assertCount(3, $exceptions);
    }
}

final class IndexedStaticModel extends StaticModel
{
    /** @var array<int, array<string, mixed>> */
    public static array $attributes = [];
    public string $key = '';
    public string $value = '';

    public function getModelAttributes(): array
    {
        return self::$attributes;
    }
}
