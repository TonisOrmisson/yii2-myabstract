<?php

namespace andmemasin\myabstract;


class Module extends \yii\base\Module
{

    /**
     * @var string The full className of the actualUser class used in project.
     * User class must have the UserStrings columns.
     */
    public $userClassName = User::class;

    /** @var string $closedTableName Closed table name */
    public $closedTableName = 'closed';

    /**
     * @return User
     */
    public function getUserClass(){

        return new $this->userClassName;
    }


}