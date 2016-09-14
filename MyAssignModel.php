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

class MyAssignModel extends MyActiveRecord
{
    /** @var string $child_fk_colname */
    public $child_fk_colname;

    /** @var string $parent_fk_colname */
    public $parent_fk_colname;

    /**
     * @param MyActiveRecord $parent
     * @param MyActiveRecord $child
     */
    public function __construct($parent,$child){
        $this->parent_fk_colname = $parent->getPrimaryKey();
        $this->child_fk_colname = $child->getPrimaryKey();
    }
}