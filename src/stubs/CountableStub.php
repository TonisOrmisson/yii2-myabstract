<?php

namespace andmemasin\myabstract\stubs;

class CountableStub implements \Countable
{
    private int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function count() : int
    {
        return $this->count;
    }

}