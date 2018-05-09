<?php

namespace andmemasin\myabstract;

class ApiResult
{
    /** @var bool $success result of the request */
    public $success = false;

    /** @var string[] $errors Error messages (if any) */
    public $errors =  [];

}