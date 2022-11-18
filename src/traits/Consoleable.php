<?php


namespace andmemasin\myabstract\traits;

use andmemasin\surveyapp\traits\ApplicationAwareTrait;
use yii\console\Application;

trait Consoleable
{
    use ApplicationAwareTrait;

    protected function log(string $message, string $level = "info")
    {
        if (\Yii::$app instanceof Application) {
            echo $message . PHP_EOL;
        }
        switch ($level) {
            case 'error':
                $this->getApp()->error($message);
                return;
            case 'warning':
                $this->getApp()->warning($message);
                return;
            case 'info':
                $this->getApp()->info($message);
                return;
            case 'debug':
                $this->getApp()->debug($message);
                return;
            default:
                throw new \Exception('Unexpected value');
        }
    }


}