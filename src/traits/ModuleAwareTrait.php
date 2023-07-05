<?php


namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Module;

/**
 * Trait ModuleTrait
 * @property-read Module $abstractModule
 * @package andmemasin\myabstract
 */
trait ModuleAwareTrait
{
    public function getAbstractModule() : Module
    {
        /** @var Module $module */
        $module = \Yii::$app->getModule('myabstract');
        return $module;
    }
}