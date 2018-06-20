<?php
namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\HasStatusModel;
use andmemasin\myabstract\StatusModel;
use yii\base\ErrorException;
use yii\base\NotSupportedException;
use yii\base\UserException;
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
    /** @var string */
    public static $hasStatusClassName;

    /** @var string */
    public static $statusModelClass = StatusModel::class;

    /** @var string */
    protected $initialStatus = StatusModel::STATUS_CREATED;

    abstract function hasMany();

    /**
     * @return boolean
     * @throws NotSupportedException
     */
    public function isActive() {
        /** @var StatusModel $statusModel */
        $statusModel = new self::$statusModelClass;
        if (method_exists($statusModel, 'isActive')) {
            return $statusModel::isActive($this->currentStatus->id);
        }
        throw new NotSupportedException('isActive missing for: ' . self::$statusModelClass);
    }

    public function addStatus($status) {
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


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHasStatuses()
    {
        /** @var HasStatusModel $hasStatus */
        $hasStatus = new static::$hasStatusClassName;
        return $this->hasMany(static::$hasStatusClassName, [$hasStatus->parentIdColumn => $hasStatus->parentIdColumn]);
    }

    /**
     * @return HasStatusModel
     */
    public function getHasStatus()
    {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new static::$hasStatusClassName;
        $query = $this->getHasStatuses();
        $query->orderBy([$hasStatusModel::primaryKey()[0]=>SORT_DESC]);
        /** @var HasStatusModel $model */
        $model = $query->one();
        return $model;
    }

    /**
     * @return StatusModel
     */
    public function getCurrentStatus()
    {
        /** @var StatusModel $class */
        $class = new static::$statusModelClass;
        return $class::getById($this->status);
    }


    /**
     * @param string $status
     * @param integer[] $model_ids
     * @throws ErrorException
     */
    public static function bulkSetStatus($status, $model_ids) {
        $query = new Query();
        /** @var StatusModel $class */
        $class = new static::$statusModelClass;

        if (!$class::isStatus($status)) {
            throw new ErrorException('Invalid Status');
        }
        $query->createCommand()
            ->update(static::tableName(), ['status'=>$status], ['in', static::primaryKeySingle(), $model_ids])
            ->execute();
    }

    /**
     * Find the Latest one HasStatus model by status
     * @param $status
     * @return StatusModel
     */
    public function findStatus($status) {
        /** @var HasStatusModel $hasStatusModel */
        $hasStatusModel = new static::$hasStatusClassName;
        $query = $this->getHasStatuses()
            ->andWhere(['status' => $status]);
        // latest first
        $query->orderBy([$hasStatusModel::primaryKeySingle() => SORT_DESC]);

        /** @var StatusModel $model */
        $model = $query->one();
        return $model;
    }

}