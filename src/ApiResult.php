<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;

class ApiResult
{
    use ConsoleAwareTrait;

    /** @var bool $success result of the request */
    public bool $success = false;

    /** @var string[] $errors Error messages (if any) */
    public array $errors = [];

    /** @var array<mixed, mixed> $data (if any) */
    public array $data = [];
}
