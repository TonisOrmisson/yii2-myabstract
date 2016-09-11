<?php


namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Module;
/**
 * Trait ModuleTrait
 * @property-read Module $module
 * @package andmemasin\surveyapp\traits
 */
trait ModuleTrait
{
    /**
     * @return Module
     */
    public static function getModule()
    {
        return \Yii::$app->getModule('myabstract');
    }
}