<?php

namespace andmemasin\myabstract;


use andmemasin\myabstract\exceptions\MyAbstractException;
use andmemasin\myabstract\interfaces\UserInterface;
use Yii;
use yii\db\ActiveQuery;

class Module extends \yii\base\Module
{

    /**
     * @var string The full className of the actualUser class used in project.
     * User class must have the UserStrings columns.
     */
    public string $userClassName = '';
    public bool $useCache = false;
    public int $defaultCacheDuration = 60;
    public bool $checkQueryClassOverridden = true;

    public function init() : void
    {
        parent::init();
        if(!$this->checkQueryClassOverridden) {
            return;
        }
        $query = Yii::$container->get(ActiveQuery::class);
        if(!($query instanceof MyActiveQuery)) {
            // we need to make sure the query->viaTable() is handled properly to check logical deletes for relations
            throw new MyAbstractException("ERROR: ActiveQuery class must be overridden with ". MyActiveQuery::class. " with this module");
        }
    }

    public function getUserClass() : UserInterface
    {
        /** @var UserInterface $model */
        $model = new $this->userClassName;
        return $model;
    }


}