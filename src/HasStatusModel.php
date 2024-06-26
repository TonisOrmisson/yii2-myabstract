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
    public string $parentClassName = '';
    public string $parentIdColumn = '';
    public string $statusModelClass = StatusModel::class;


    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->parentClassName) {
            throw new InvalidConfigException('parentClassName must be set for ' . static::class);
        }

        /** @var MyActiveRecord $parent */
        $parent = \Yii::createObject($this->parentClassName);
        $this->parentIdColumn = $parent::primaryKey()[0];


        parent::init();
    }

    public function getStatusModel() : StatusModel
    {
        /** @var StatusModel $statusModel */
        $statusModel = \Yii::createObject($this->statusModelClass);
        $result = $statusModel::getById($this->status);
        if($result === null) {
            throw new InvalidConfigException('Status not found for ' . $this->status);
        }
        return $result;
    }

}