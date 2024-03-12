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
    public function getKey() : mixed;
    public function getValue() : mixed;
    public function setKey(mixed $key) : void;
    public function setValue(mixed $value) : void;
    public function save() : bool;

}