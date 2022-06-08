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

    public function getUserCreated() : User
    {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userCreatedCol});
    }

    public function getUserUpdated() : ?User
    {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userUpdatedCol});
    }

    /**
     * Get User who last closed (deleted) the record
     */
    public function getUserClosed() : ?User
    {
        /** @var User $userClassName */
        $userClassName = $this->getAbstractModule()->userClassName;
        return $userClassName::findOne($this->{$this->userClosedCol});
    }

    /**
     * Get Time record was created
     * @return string datetime(6)
     */
    public function getTimeCreated() : string
    {
        return $this->{$this->timeCreatedCol};
    }

    /**
     * Get Time record was updated
     * @return string|null datetime(6)
     */
    public function getTimeUpdated() : ?string
    {
        return $this->{$this->timeUpdatedCol};
    }

    /**
     * Get Time record was closed (deleted)
     * @return string|null datetime(6)
     */
    public function getTimeClosed() : ?string
    {
        return $this->{$this->timeClosedCol};
    }


    public function getRelationCount(string $className, ?string $idColumn = null, array $filters = []) : int
    {
        if ($idColumn !== null) {
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
        if (count($filters) > 0) {
            $query->andWhere($filters);
        }
        return $query->count();

    }

    /**
     * @param mixed $condition
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
