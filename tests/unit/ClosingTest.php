<?php
namespace andmemasin\myabstract;

class ClosingTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var Closing */
    private $model;

    protected function _before()
    {
        $this->model = new Closing();
    }


    public function testTableName()
    {
        $this->assertEquals('{{closing}}', $this->model->tableName());
    }
    public function testRules()
    {
        $this->assertInternalType('array', $this->model->rules());
    }
}