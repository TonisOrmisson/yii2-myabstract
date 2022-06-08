<?php

namespace andmemasin\myabstract;

use yii\base\Model;

interface TypeInterface
{
    public static function getByKey(string $key) : ?Model;

    /**
     * @return string[]
     */
    public static function primaryKey() : array;

    /**
     * Name of the single primary key field
     */
    public function primaryKeySingle() : string;

}