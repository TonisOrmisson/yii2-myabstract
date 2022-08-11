<?php

namespace andmemasin\myabstract\traits;

use Yii;
use yii\console\Application;

/**
 * Class ConsoleAwareTrait
 * @package andmemasin\myabstract\traits

 * @property boolean $isConsole whether we currently run in console app or not
 *
 */
trait ConsoleAwareTrait
{

    public function getIsConsole() : bool
    {
        return Yii::$app instanceof Application;
    }

}