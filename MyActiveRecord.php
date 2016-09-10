<?php
/**
 * @link http://datuno.com/
 * @copyright Copyright (c) 2016 Andmemasin OÜ
 */

namespace andmemasin\myabstract;

use yii;
use app\models\user\User;
use yii\db\ActiveRecord;

/**
 * A wrapper class do have all models woth custom features
 *
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class MyActiveRecord extends ActiveRecord
{
    const END_OF_TIME = '3000-12-31 00:00:00.000000';
    
    use MyActiveTrait;


    /**
     * Get User who created the record
     * @return \app\models\user\User
     */
    public function getUserCreated() {
        return User::findOne($this->{$this->userCreatedCol});
    }
    /**
     * Get User who last updated the record
     * @return \app\models\user\User
     */
    public function getUserUpdated() {
        return User::findOne($this->{$this->userUpdatedCol});
    }
    /**
     * Get User who last closed (deleted) the record
     * @return \app\models\user\User
     */
    public function getUserClosed() {
        return User::findOne($this->{$this->userClosedCol});
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
    
}