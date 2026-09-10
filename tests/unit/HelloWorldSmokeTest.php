<?php

declare(strict_types=1);

namespace andmemasin\myabstract;

use Codeception\Test\Unit;

final class HelloWorldSmokeTest extends Unit
{
    public function testHelloWorld(): void
    {
        self::assertSame('Hello world', sprintf('Hello %s', 'world'));
    }
}
