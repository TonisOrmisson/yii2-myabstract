<?php


namespace andmemasin\myabstract\traits;

use yii\console\Application;

/**
 * @deprecated use ApplicationAwareTrait
 */
trait Consoleable
{

    protected function log(string $message, string $level = "info")
    {
        if (\Yii::$app instanceof Application) {
            echo $message . PHP_EOL;
        }
        switch ($level) {
            case 'error':
                return \Yii::error($message, __METHOD__);
            case 'warning':
                return \Yii::warning($message, __METHOD__);
            case 'info':
                return \Yii::info($message, __METHOD__);
            case 'debug':
            case 'trace':
                return \Yii::debug($message, __METHOD__);
            default:
                throw new \Exception('Unexpected value');
        }
    }


}