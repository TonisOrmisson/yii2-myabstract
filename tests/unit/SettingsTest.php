<?php
namespace andmemasin\myabstract;

class SettingsTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var Settings */
    private $model;
    
    protected function _before()
    {
        $this->model = new Settings(['itemClass' => Setting::class]);

    }

    protected function _after()
    {
    }

    /**
     * @expectedException yii\base\InvalidArgumentException
     */
    public function testInit()
    {
        $this->model = new Settings();
    }
}