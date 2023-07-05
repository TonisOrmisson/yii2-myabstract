<?php


namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Module;
use yii\caching\CacheInterface;

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

    public function getCache() : CacheInterface
    {
        $cache = \Yii::$app->cache;
        if($cache === null) {
            throw new \Exception("no cache!");
        }
        return $cache;
    }
}