<?php

namespace andmemasin\myabstract\interfaces;

interface UserInterface
{

    public function getUsername() : string;
    public function id() : string|int;

    /**
     * @param mixed $condition
     * @return ?UserInterface
     */
    public static function findOne(mixed $condition);

}