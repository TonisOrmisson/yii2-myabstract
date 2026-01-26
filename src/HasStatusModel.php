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
    /** @var class-string<MyActiveRecord>|'' */
    public string $parentClassName = '';
    public string $parentIdColumn = '';
    /** @var class-string<StatusModel> */
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

        /** @var class-string<MyActiveRecord> $parentClassName */
        $parentClassName = $this->parentClassName;
        /** @var MyActiveRecord $parent */
        $parent = \Yii::createObject($parentClassName);
        $this->parentIdColumn = $parent::primaryKey()[0];


        parent::init();
    }

    public function getStatusModel() : StatusModel
    {
        /** @var class-string<StatusModel> $statusModelClass */
        $statusModelClass = $this->statusModelClass;
        /** @var StatusModel $statusModel */
        $statusModel = \Yii::createObject($statusModelClass);
        $result = $statusModelClass::getById($this->status);
        if($result === null) {
            throw new InvalidConfigException('Status not found for ' . $this->status);
        }
        return $result;
    }

}
