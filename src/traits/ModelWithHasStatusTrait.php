<?php
namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\HasStatusModel;
use andmemasin\myabstract\StatusModel;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\db\ActiveQueryInterface;
use yii\db\Query;

/**
 * Trait ModelWithHasStatusTrait
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 *
 * @property HasStatusModel[] $hasStatuses
 * @property StatusModel $currentStatus
 * @property HasStatusModel $hasStatus
 */
trait ModelWithHasStatusTrait
{

    /**
     * @return boolean
     */
    public function isActive() : bool
    {
        /** @var StatusModel $statusModel */
        $statusModel = (new static)->statusModelClass;
        return (new $statusModel)->isActive($this->currentStatus->id);
    }

    public function addStatus(string $status) : void
    {
        /** @var HasStatusModel $hasStatus */
        $hasStatus = new static::$hasStatusClassName;
        $hasStatus->status = $status;
        $hasStatus->{$hasStatus->parentIdColumn} = static::getPrimaryKey();

        if (!$hasStatus->save()) {
            throw new UserException(serialize($hasStatus->errors));
        }
    }

    /** {@inheritdoc} */
    public function afterSave($insert, $changedAttributes)
    {

        if ($insert) {
            // add a created status im the status history if some other status is assigned
            if ($this->status != $this->initialStatus) {
                $this->addStatus($this->initialStatus);
            }
            $this->addStatus($this->status);
        } else {
            if (isset($changedAttributes['status'])) {
                $this->addStatus($this->status);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }


    public function getHasStatuses() : ActiveQueryInterface
    {
        /** @var HasStatusModel $hasStatus */
        $hasStatus = new static::$hasStatusClassName;
        return $this->hasMany(static::$hasStatusClassName, [$hasStatus->parentIdColumn => $hasStatus->parentIdColumn]);
    }

    public function getHasStatus() : HasStatusModel
    {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new static::$hasStatusClassName;
        $query = $this->getHasStatuses();
        $query->orderBy([$hasStatusModel->primaryKeySingle()=>SORT_DESC]);
        /** @var ?HasStatusModel $model */
        $model = $query->limit(1)->one();
        return $model;
    }

    public function getCurrentStatus() : StatusModel
    {
        /** @var StatusModel $class */
        $class = (new static)->statusModelClass;
        return $class::getById($this->status);
    }


    public static function bulkSetStatus(string $status, array $model_ids = []) : int
    {
        $query = new Query();
        /** @var StatusModel $class */
        $class = (new static)->statusModelClass;

        if (!$class::isStatus($status)) {
            throw new ErrorException('Invalid Status');
        }
        return $query->createCommand()
            ->update(static::tableName(), ['status'=>$status], ['in', (new static)->primaryKeySingle(), $model_ids])
            ->execute();
    }

    /**
     * Find the Latest one HasStatus model by status
     */
    public function findStatus(string $status) : ?StatusModel
    {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new static::$hasStatusClassName;
        $query = $this->getHasStatuses()
            ->andWhere(['status' => $status]);
        // latest first
        $query->orderBy([$hasStatusModel->primaryKeySingle() => SORT_DESC]);

        /** @var ?StatusModel $model */
        $model = $query->limit(1)->one();
        return $model;
    }

}