<?php


namespace andmemasin\myabstract\traits;

use yii\console\Application;

trait Consoleable
{
    /**
     * @param $message
     * @param string $level
     */
    protected function log($message, $level = "info")
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
                return \Yii::debug($message, __METHOD__);
            default:
                throw new \Exception('Unexpected value');
        }
    }


}