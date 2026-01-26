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

    /** @var class-string<StatusModel>|'' */
    public string $statusModelClass = '';
    /** @var class-string<HasStatusModel>|'' */
    public static string $hasStatusClassName = '';
    protected string $initialStatus = StatusModel::STATUS_CREATED;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!static::$hasStatusClassName) {
            throw new InvalidConfigException('hasStatusClassName must be set for ' . static::class);
        }

        parent::init();
    }


}
