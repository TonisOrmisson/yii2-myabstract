<?php

namespace andmemasin\myabstract\interfaces;


use yii\data\BaseDataProvider;
use yii\data\DataProviderInterface;

interface Searchable
{

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params) : DataProviderInterface;

}