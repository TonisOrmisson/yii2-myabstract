<?php
/**
 * This defines the main methods that a generel user class must have
 * if it wants to extend the MyActiveRecord class
 * User: tonis_o
 * Date: 10.09.16
 * Time: 11:41
 */

namespace andmemasin\myabstract;


interface MyUserInterface
{
    public function findOne($id);
}