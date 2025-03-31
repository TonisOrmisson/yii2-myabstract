<?php

namespace andmemasin\myabstract\events;

use andmemasin\myabstract\MyActiveRecord;
use yii\base\Event;

/**
 * Class MyActiveRecordEvent
 * @package andmemasin\myabstract\events
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class MyActiveRecordEvent extends Event
{

    const EVENT_AFTER_SOFT_DELETE = 'afterSoftDelete';

    public function __construct(
        protected MyActiveRecord $model,
    )
    {
    }

    public function getModel(): MyActiveRecord
    {
        return $this->model;
    }

}