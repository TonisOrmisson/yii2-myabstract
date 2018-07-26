<?php
namespace andmemasin\myabstract;

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

    // tests
    public function testGetModels()
    {
        $this->assertEquals([], $this->model::getModels());
    }
}