<?php

namespace andmemasin\myabstract;

use andmemasin\surveybasemodels\Status;
use yii\base\InvalidConfigException;
use andmemasin\survey\api\Status as BaseStatus;
use yii\base\UserException;

/**
 * Class ModelWithHasStatus
 *
 * @property string $status
 *
 * @property HasStatusModel[] $hasStatuses
 * @property Status $statusModel
 * @property Status $currentStatus
 * @property HasStatusModel $hasStatus
 *
 * @package andmemasin\myabstract
 */
class ModelWithHasStatus extends MyActiveRecord
{
    public $hasStatusClassName;

    public function init()
    {
        if(!$this->hasStatusClassName){
            throw new InvalidConfigException('hasStatusClassName must be set for '.static::className());
        }
        parent::init();
    }

    public function isActive(){
        return in_array($this->currentStatus->status,BaseStatus::getActiveStatuses());
    }




    protected function addStatus($status){
        /** @var HasStatusModel $hasStatus */
        $hasStatus = new $this->hasStatusClassName;
        $hasStatus->status = $status;
        $hasStatus->{$hasStatus->parentIdColumn} = static::getPrimaryKey();

        if (!$hasStatus->save()) {
            throw new UserException(serialize($hasStatus->errors));
        }
    }
    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        $this->addStatus($this->status);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHasStatuses()
    {
        /** @var HasStatusModel $hasStatus */
        $hasStatus = new $this->hasStatusClassName;
        return $this->hasMany($this->hasStatusClassName, [$hasStatus->parentIdColumn => $hasStatus->parentIdColumn]);
    }

    /**
     * @return HasStatusModel
     */
    public function getHasStatus()
    {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new $this->hasStatusClassName;
        $query = $this->getHasStatuses();
        $query->orderBy([$hasStatusModel::primaryKey()[0]=>SORT_DESC]);
        /** @var HasStatusModel $model */
        $model = $query->one();
        return $model;
    }

    /**
     * @return Status
     */
    public function getCurrentStatus()
    {
        return Status::findOne($this->hasStatus->status);
    }


}