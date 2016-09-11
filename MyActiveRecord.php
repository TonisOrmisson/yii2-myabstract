<?php
/**
 * @link http://datuno.com/
 * @copyright Copyright (c) 2016 Andmemasin OÜ
 */

namespace andmemasin\myabstract;

use yii;
use yii\db\ActiveRecord;
use andmemasin\myabstract\traits\ModuleTrait;
use andmemasin\myabstract\User;
use andmemasin\myabstract\traits\MyActiveTrait;
/**
 * A wrapper class do have all models with custom features
 *
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class MyActiveRecord extends ActiveRecord
{
    const END_OF_TIME = '3000-12-31 00:00:00.000000';
    use MyActiveTrait;
    use ModuleTrait;

    /**
     * Get User who created the record
     * @return User
     */
    public function getUserCreated() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->userCreatedCol});
    }
    /**
     * Get User who last updated the record
     * @return User
     */
    public function getUserUpdated() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->userUpdatedCol});
    }
    /**
     * Get User who last closed (deleted) the record
     * @return User
     */
    public function getUserClosed() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->userClosedCol});
    }

    /**
     * Get Time record was created
     * @return String datetime(6)
     */
    public function getTimeCreated() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->timeCreatedCol});
    }

    /**
     * Get Time record was updated
     * @return String datetime(6)
     */
    public function getTimeUpdated() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->timeUpdatedCol});
    }

    /**
     * Get Time record was closed (deleted)
     * @return String datetime(6)
     */
    public function getTimeClosed() {
        $userClassName = ModuleTrait::getModule()->userClassName;
        return $userClassName::findOne($this->{$this->timeClosedCol});
    }

}