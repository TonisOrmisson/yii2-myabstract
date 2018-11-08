<?php
namespace andmemasin\myabstract;

use Codeception\Stub;

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
}