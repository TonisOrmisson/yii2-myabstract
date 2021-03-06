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
    public static function getAllStatusNames();

    /**
     * @param string $id
     * @return boolean
     */
    public static function isStatus($id);

    /**
     * @param string $id
     * @return string
     */
    public static function getStatusLabel($id);

    /**
     * @return boolean
     */
    public function isActive($id);

}