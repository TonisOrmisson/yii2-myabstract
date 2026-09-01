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

    public function testCurrentDatetimeUsesRuntimeTimezoneAndMicroseconds(): void
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('Pacific/Chatham');

        try {
            $before = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');
            $current = $this->invokeMethod($this->model, 'currentDatetime');
            $after = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');

            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $current);
            $this->assertGreaterThanOrEqual($before, $current);
            $this->assertLessThanOrEqual($after, $current);
        } finally {
            date_default_timezone_set($timezone);
        }
    }
}

final class MyActiveTraitTestRecord extends MyActiveRecord
{
    public static function tableName(): string
    {
        return 'my_active_trait_test_record';
    }
}
