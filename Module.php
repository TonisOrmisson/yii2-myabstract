<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 10.09.16
 * Time: 11:46
 */

namespace andmemasin\myabstract;


class Module extends \yii\base\Module
{

    /**
     * @var string The full classname of the actualUser class used in project.
     * User class must have the UserStrings columns.
     */
    public $userClassName = 'andmemasin\myabstract\User';

    /** @var string $closedTableName Closed table name */
    public $closedTableName = 'closed';

    /**
     * @return User
     */
    public function getUserClass(){

        return new $this->userClassName;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

}