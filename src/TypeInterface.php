<?php

namespace andmemasin\myabstract;

use yii\base\Model;

interface TypeInterface
{
    /**
     * @param string $key
     * @return Model
     */
    public static function getByKey($key);

    /**
     * @return string[]
     */
    public static function primaryKey();

    /**
     * Name of the single primary key field
     * @return string
     */
    public static function primaryKeySingle();

}