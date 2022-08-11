<?php

namespace andmemasin\myabstract\interfaces;

/**
 * Interface SettingInterface
 * @package andmemasin\myabstract\src\interfaces
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
interface SettingInterface
{

    public function findOneByKey(string $key) : ?static;

}