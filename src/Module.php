<?php

namespace andmemasin\myabstract;


use andmemasin\myabstract\interfaces\UserInterface;

class Module extends \yii\base\Module
{

    /**
     * @var string The full className of the actualUser class used in project.
     * User class must have the UserStrings columns.
     */
    public string $userClassName = '';

    /** @var string $closedTableName Closed table name */
    public string $closedTableName = 'closed';

    public function getUserClass() : UserInterface
    {
        /** @var UserInterface $model */
        $model = new $this->userClassName;
        return $model;
    }


}