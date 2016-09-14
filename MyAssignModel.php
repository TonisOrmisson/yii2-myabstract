<?php
/**
 * This is a base class for models that bind/assign child models to
 * parent models. Typically named as ParentHasChild pattern
 *
 * User: tonis_o
 * Date: 14.09.16
 * Time: 21:21
 */

namespace andmemasin\myabstract;


use andmemasin\myabstract\MyActiveRecord;

abstract class MyAssignModel extends MyActiveRecord
{

    /**
     * @param MyActiveRecord $parent
     * @param MyActiveRecord $child
     * @return mixed
     */
    abstract public function assign($parent, $child);
}