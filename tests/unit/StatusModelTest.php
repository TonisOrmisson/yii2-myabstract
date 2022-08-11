<?php
namespace andmemasin\myabstract;

use Codeception\Stub;
use yii\helpers\ArrayHelper;

class StatusModelTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\myabstract\UnitTester
     */
    protected $tester;

    /** @var StatusModel */
    private $model;

    protected function _before()
    {
        $this->model = new StatusModel();
    }

    protected function _after()
    {
    }

    public function testGetModels()
    {
        $this->assertEquals([StatusModel::STATUS_CREATED], array_keys($this->model->getModelAttributes()));
    }

    public function testGetAllStatusNames()
    {
        $this->assertEquals(['Created'], $this->model->getAllStatusNames());
    }

    public function testIsStatusSuccess()
    {
        $this->assertTrue($this->model->isStatus(StatusModel::STATUS_CREATED));
    }

    public function testIsStatusFailsNonExisting()
    {
        $this->assertFalse($this->model->isStatus('not-existing-status'));
    }

    public function testGetStatusLabelSuccess()
    {
        $this->assertEquals('Created', $this->model->getStatusLabel(StatusModel::STATUS_CREATED));
    }

    public function testGetStatusLabelFailsNotExisting()
    {
        $this->assertEquals('', $this->model->getStatusLabel('not-existing-status'));
    }

    public function testIsActive()
    {
        $this->expectException(\Exception::class);
        $this->assertNull($this->model->isActive('not-existing-status'));
    }
}
