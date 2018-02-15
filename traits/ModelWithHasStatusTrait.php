<?php
namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\HasStatusModel;
use yii\base\ErrorException;
use yii\base\UserException;
use andmemasin\survey\Status;
use yii\db\Query;

/**
 * Trait ModelWithHasStatusTrait
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
trait ModelWithHasStatusTrait
{
    public $hasStatusClassName;
    protected $initialStatus;

    public function isActive(){
        return Status::isActive($this->currentStatus->id);
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

        if($insert){
            // add a created status im the status hostory if some other status is assigned
            if($this->status != Status::STATUS_CREATED){
                $this->addStatus(Status::STATUS_CREATED);
            }
            $this->addStatus($this->status);
        }else{
            if(isset($changedAttributes['status'])){
                $this->addStatus($this->status);
            }
        }
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
        return Status::getById($this->status);
    }


    /**
     * @param string $status
     * @param integer[] $model_ids
     * @throws ErrorException
     */
    public static function bulkSetStatus($status, $model_ids){
        $query = new Query();

        if(!Status::isStatus($status)){
            throw new ErrorException('Invalid Status');
        }
        $query->createCommand()
            ->update(self::tableName(),['status'=>$status],['in',self::primaryKey()[0],$model_ids])
            ->execute();
    }

}