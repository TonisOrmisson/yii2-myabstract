<?php
namespace andmemasin\myabstract;

use andmemasin\myabstract\test\InvokeProtectedTrait;
class MyActiveTraitTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    use InvokeProtectedTrait;

    private MyActiveTraitTestRecord $model;
    
    protected function _before()
    {
        $this->model = new MyActiveTraitTestRecord();
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

final class MyActiveTraitTestRecord extends MyActiveRecord
{
    public static function tableName(): string
    {
        return 'my_active_trait_test_record';
    }
}
