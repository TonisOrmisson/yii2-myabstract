<?php

namespace andmemasin\myabstract\interfaces;


use yii\data\BaseDataProvider;

interface Searchable
{

    /**
     * @param array $params
     *
     * @return BaseDataProvider
     */
    public function search($params);

}