<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;
use yii;
use andmemasin\myabstract\traits\ModuleTrait;
use andmemasin\myabstract\traits\MyActiveTrait;

/**
 * A wrapper class do have all models with custom features
 *
 * @property User $userCreated
 * @property User $userUpdated
 * @property User $userClosed
 *
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class MyActiveRecord extends ActiveRecord
{

    use MyActiveTrait;
    use ModuleTrait;
    use ConsoleAwareTrait;

    /**
     * Get User who created the record
     * @return User
     */
    public function getUserCreated() {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userCreatedCol});
    }
    /**
     * Get User who last updated the record
     * @return User
     */
    public function getUserUpdated() {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userUpdatedCol});
    }
    /**
     * Get User who last closed (deleted) the record
     * @return User
     */
    public function getUserClosed() {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userClosedCol});
    }

    /**
     * Get Time record was created
     * @return String datetime(6)
     */
    public function getTimeCreated() {
        return $this->{$this->timeCreatedCol};
    }

    /**
     * Get Time record was updated
     * @return String datetime(6)
     */
    public function getTimeUpdated() {
        return $this->{$this->timeUpdatedCol};
    }

    /**
     * Get Time record was closed (deleted)
     * @return String datetime(6)
     */
    public function getTimeClosed() {
        return $this->{$this->timeClosedCol};
    }


    /**
     * @param string $className
     * @param string $idColumn
     * @param array $filters
     * @return mixed
     */
    public function getRelationCount($className, $idColumn = null, $filters = null) {
        if (!$idColumn) {
            $idColumn = $this->tableName() . "_id";
        }

        $config = [
            'class' => $className,
        ];

        /** @var MyActiveRecord $model */
        $model = Yii::createObject($config);
        /** @var ActiveRecord $className */
        $query = $model->query()
            ->from($className::tableName())
            ->andWhere([$idColumn => $this->primaryKey]);
        if ($filters) {
            $query->andWhere($filters);
        }
        return $query->count();

    }

    /**
     * @param mixed $condition
     * @return static|null
     */
    public static function findOne($condition)
    {
        /**
         * primary keys must always be integers and we cast the param to int
         * for input sanitizing if its not an array
         */
        if(!is_array($condition)) {
            return parent::findOne((int) $condition);
        }
        return parent::findOne($condition);
    }


    /**
     * {@inheritdoc}
     * @return static[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAll($condition)
    {
        /**
         * primary keys must always be integers and we cast the param to int
         * for input sanitizing if its not an array
         */
        if(!is_array($condition)) {
            return parent::findAll((int) $condition);
        }
        return parent::findAll($condition);
    }

}
