<?php

namespace andmemasin\myabstract\stubs;

class CountableStub implements \Countable
{
    /** @var int<0, max> */
    private int $count;

    public function __construct(int $count)
    {
        $this->count = max(0, $count);
    }

    /**
     * @return int<0, max>
     */
    public function count() : int
    {
        return $this->count;
    }

}
