<?php

namespace andmemasin\myabstract;


class Module extends \yii\base\Module
{

    /**
     * @var string The full className of the actualUser class used in project.
     * User class must have the UserStrings columns.
     */
    public string $userClassName = User::class;

    /** @var string $closedTableName Closed table name */
    public string $closedTableName = 'closed';

    public function getUserClass() : User
    {
        /** @var User $model */
        $model = new $this->userClassName;
        return $model;
    }


}