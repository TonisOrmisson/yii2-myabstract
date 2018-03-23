<?php

namespace andmemasin\myabstract;

use andmemasin\survey\Status;
use yii\base\InvalidConfigException;

/**
 * Class HasStatusModel
 *
 * @property string $status
 *
 * @property Status $statusModel
 * @package andmemasin\myabstract
 */
class HasStatusModel extends MyActiveRecord
{
    public $parentClassName;
    public $parentIdColumn;



    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if(!$this->parentClassName){
            throw new InvalidConfigException('parentClassName must be set for '.static::class);
        }

        /** @var MyActiveRecord $parent */
        $parent = new $this->parentClassName;
        $this->parentIdColumn = $parent::primaryKey()[0];


        parent::init();
    }

    /**
     * @return Status
     */
    public function getStatusModel()
    {
        return Status::getById($this->status);
    }

}