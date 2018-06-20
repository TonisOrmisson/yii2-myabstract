<?php

namespace andmemasin\myabstract;

use yii\base\InvalidConfigException;

/**
 * Class HasStatusModel
 *
 * @property string $status
 *
 * @property StatusModel $statusModel
 * @package andmemasin\myabstract
 */
class HasStatusModel extends MyActiveRecord
{
    public $parentClassName;
    public $parentIdColumn;

    /** @var string */
    public static $statusModelClass = StatusModel::class;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->parentClassName) {
            throw new InvalidConfigException('parentClassName must be set for ' . static::class);
        }

        /** @var MyActiveRecord $parent */
        $parent = new $this->parentClassName;
        $this->parentIdColumn = $parent::primaryKey()[0];


        parent::init();
    }

    /**
     * @return StatusModel
     */
    public function getStatusModel()
    {
        /** @var StatusModel $statusModel */
        $statusModel = new static::$statusModelClass;
        return $statusModel::getById($this->status);
    }

}