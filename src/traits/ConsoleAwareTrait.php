<?php

namespace andmemasin\myabstract\traits;

use Yii;

/**
 * Class ConsoleAwareTrait
 * @package andmemasin\myabstract\traits

 * @property boolean $isConsole whether we currently run in console app or not
 *
 */
trait ConsoleAwareTrait
{
    /**
     * @return boolean
     */
    public function getIsConsole()
    {
        return Yii::$app instanceof yii\console\Application;
    }

}