<?php
namespace andmemasin\myabstract;

use yii\helpers\ArrayHelper;

class ModelWithHasStatusTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var ModelWithHasStatus */
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
        new ModelWithHasStatus();
    }


}