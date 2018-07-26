<?php
namespace andmemasin\myabstract;

use andmemasin\myabstract\test\InvokeProtectedTrait;
use andmemasin\myabstract\traits\MyActiveTrait;

class MyActiveTraitTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    use InvokeProtectedTrait;

    /** @var MyActiveTrait */
    private $model;
    
    protected function _before()
    {
        $this->model = $this->getMockForTrait(MyActiveTrait::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testUserId()
    {
        $result = $this->invokeMethod($this->model, 'userId');
        $this->assertEquals(1, $result);
    }

    public function testLabel() {
        $this->assertEquals("", $this->model->label());
    }
}