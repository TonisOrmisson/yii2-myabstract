<?php

namespace andmemasin\myabstract\interfaces;

interface UserInterface
{

    public function getUsername() : string;
    public function id() : string|int;


}