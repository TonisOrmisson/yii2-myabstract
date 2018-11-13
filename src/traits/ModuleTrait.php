<?php


namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Module;

/**
 * Trait ModuleTrait
 * @property-read Module $abstractModule
 * @package andmemasin\myabstract
 */
trait ModuleTrait
{
    /**
     * @return Module
     */
    public function getAbstractModule()
    {
        /** @var Module $module */
        $module = \Yii::$app->getModule('myabstract');
        return $module;
    }
}