<?php

namespace andmemasin\myabstract;

use andmemasin\surveybasemodels\Status;
use yii\base\InvalidConfigException;
use andmemasin\survey\api\Status as BaseStatus;

/**
 * Class ModelWithHasStatus
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
     * @return \yii\db\ActiveQuery
     */
    public function getHasStatus()
    {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new $this->hasStatusClassName;
        $query = $this->getHasStatuses();
        $query->orderBy([$hasStatusModel->primaryKey[0]=>SORT_DESC]);
        return $query->limit(1);
    }

    /**
     * @return Status
     */
    public function getCurrentStatus()
    {
        return Status::findOne($this->hasStatus->status);
    }


}