<?php

namespace andmemasin\myabstract;

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