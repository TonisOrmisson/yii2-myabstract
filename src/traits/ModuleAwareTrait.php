<?php


namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Module;
use yii\base\InvalidConfigException;
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
        $app = \Yii::$app;
        if ($app === null) {
            throw new InvalidConfigException('Yii application is not configured');
        }

        $module = $app->getModule('myabstract');
        if (!$module instanceof Module) {
            throw new InvalidConfigException('Yii module "myabstract" must be an instance of ' . Module::class);
        }

        return $module;
    }

    public function getCache() : ?CacheInterface
    {
        $app = \Yii::$app;
        if ($app === null) {
            throw new InvalidConfigException('Yii application is not configured');
        }

        $cache = $app->get('cache', false);
        if($cache === null) {
            return null;
        }
        if (!$cache instanceof CacheInterface) {
            throw new InvalidConfigException('Yii cache component must implement ' . CacheInterface::class);
        }

        return $cache;
    }
}
