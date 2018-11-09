<?php
namespace andmemasin\myabstract;

class HasStatusModelTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var HasStatusModel */
    private $model;

    protected function _before()
    {
        //$this->model = new HasStatusModel(['parentClassName']);
    }


    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testInitializingWithoutParentClassThrowsException()
    {
        new HasStatusModel();
    }

}