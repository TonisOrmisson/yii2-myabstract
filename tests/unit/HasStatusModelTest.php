<?php
namespace andmemasin\myabstract;

use yii\base\InvalidConfigException;

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


    public function testInitializingWithoutParentClassThrowsException()
    {
        $this->expectException(InvalidConfigException::class);
        new HasStatusModel();
    }

}
