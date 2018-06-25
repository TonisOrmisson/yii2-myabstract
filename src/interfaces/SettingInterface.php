<?php

namespace andmemasin\myabstract\src\interfaces;

/**
 * Interface SettingInterface
 * @package andmemasin\myabstract\src\interfaces
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
interface SettingInterface
{

    /**
     * @param $key string
     * @return static
     */
    public function findOneByKey($key);

}