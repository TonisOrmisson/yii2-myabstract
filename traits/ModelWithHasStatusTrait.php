<?php
namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\HasStatusModel;
use yii\base\UserException;
use andmemasin\survey\Status;

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
        return Status::isActive($this->currentStatus->status);
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
            $this->addStatus(Status::STATUS_CREATED);
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

}