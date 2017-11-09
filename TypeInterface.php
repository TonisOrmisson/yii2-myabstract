<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 8.11.17
 * Time: 10:10
 */

namespace app\modules\myabstract;


interface TypeInterface
{
    /**
     * @param string $key
     * @return static
     */
    public static function getByKey($key);

    /**
     * @return string[]
     */
    public static function primaryKey();
}