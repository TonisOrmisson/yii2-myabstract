<?php

namespace andmemasin\myabstract\interfaces;

use yii\base\NotSupportedException;

interface OnePrimaryKeyInterface
{

    /**
     * Get the primary key column as string if the one-column PK
     * NB! Always use single column Primary-keys!
     * NB! this assumes that primary key always has the table_name_id format
     * @throws NotSupportedException if multi-column PrimaryKey is used
     */
    public static function primaryKeySingle(): string;

    public static function cahceDepencencyTagsOne(int|string $primaryKey) : array;

}