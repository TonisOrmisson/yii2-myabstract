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

class MyAssignModel extends MyActiveRecord
{
    /* @var $parentIdColumnName string Column name containing parent id FK */
    public $parentIdColumnName;

    /* @var $childIdColumnName string Column name containing child id FK */
    public $childIdColumnName;

    /**
     * @param MyActiveRecord $parent
     * @param MyActiveRecord $child
     * @return mixed
     */
    public function assign($parent, $child){
        $this->{$this->parentIdColumnName} = $parent->primaryKey();
        $this->{$this->childIdColumnName}=$child->primaryKey();
    }
}