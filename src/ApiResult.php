<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;

class ApiResult
{
    use ConsoleAwareTrait;

    /** @var bool $success result of the request */
    public $success = false;

    /** @var string[] $errors Error messages (if any) */
    public $errors = [];

}