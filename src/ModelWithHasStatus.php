<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ModelWithHasStatusTrait;
use yii\base\InvalidConfigException;

/**
 * Class ModelWithHasStatus
 *
 * @property string $status
 *
 *
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class ModelWithHasStatus extends MyActiveRecord
{
    use ModelWithHasStatusTrait;


    public function init()
    {
        if (!static::$hasStatusClassName) {
            throw new InvalidConfigException('hasStatusClassName must be set for ' . static::class);
        }

        parent::init();
    }


}