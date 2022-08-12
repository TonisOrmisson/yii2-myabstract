<?php

namespace andmemasin\myabstract\events;

use andmemasin\myabstract\MyActiveRecord;
use yii\base\Event;

/**
 * Class MyAssignmentEvent
 * @package andmemasin\myabstract\events
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class MyAssignmentEvent extends Event
{
    public MyActiveRecord $item;
}