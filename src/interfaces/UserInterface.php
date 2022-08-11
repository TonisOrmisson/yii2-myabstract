<?php

namespace andmemasin\myabstract\interfaces;

interface UserInterface
{

    public function getUsername() : string;
    public function id() : string|int;
    public static function findOne(mixed $condition) : ?static;

}