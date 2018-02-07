<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 7.02.18
 * Time: 21:34
 */

namespace app\modules\andmemasin\myabstract\stubs;

class CountableStub implements \Countable
{
    private $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function count()
    {
        return $this->count;
    }

}