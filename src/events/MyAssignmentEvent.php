<?php

namespace andmemasin\myabstract\events;

use yii\base\Event;
use yii\db\ActiveRecordInterface;

/**
 * Class MyAssignmentEvent
 * @package andmemasin\myabstract\events
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class MyAssignmentEvent extends Event
{
    public ActiveRecordInterface $item;
}