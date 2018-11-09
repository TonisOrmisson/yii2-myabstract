<?php
namespace andmemasin\myabstract;

class SettingTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var Setting */
    private $model;

    protected function _before()
    {
        $this->model = new Setting();
    }

    protected function _after()
    {
    }

    // tests
    public function testRulesReturnsArray()
    {
        $this->assertInternalType('array', $this->model->rules());

    }
}