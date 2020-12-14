<?php
namespace andmemasin\myabstract;


use yii\base\InvalidConfigException;

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


    public function testInitializingWithoutParentClassThrowsException()
    {
        $this->expectException(InvalidConfigException::class);
        new ModelWithHasStatus();
    }


}
