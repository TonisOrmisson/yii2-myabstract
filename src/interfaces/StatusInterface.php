<?php

namespace andmemasin\myabstract\interfaces;

/**
 * Interface StatusInterface
 * @package andmemasin\myabstract
 */
interface StatusInterface
{

    /**
     * @return string[]
     */
    public static function getAllStatusNames() : array;
    public static function isStatus(string $id) : bool;
    public static function getStatusLabel(string $id) : string;
    public function isActive(string $id) : bool;

}