<?php
namespace andmemasin\myabstract;

class ApiResultTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var ApiResult */
    private $model;

    protected function _before()
    {
        $this->model = new ApiResult();
    }

    protected function _after()
    {
    }

    // tests
    public function testDefaultSuccessIsFalse()
    {
        $this->assertFalse($this->model->success);

    }
    public function testDefaultErrorsIsEmpty()
    {
        $this->assertEquals([], $this->model->errors);

    }
}