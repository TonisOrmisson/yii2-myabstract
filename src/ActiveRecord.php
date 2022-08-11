<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\base\NotSupportedException;

/**
 * Class ActiveRecord
 * @package andmemasin\myabstract
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class ActiveRecord extends BaseActiveRecord
{
    use ConsoleAwareTrait;

    public bool $isSearchModel = false;

    /**
     * Get the primary key column as string if the one-column PK
     * NB! Always use single column Primary-keys!
     * NB! this assumes that primary key always has the table_name_id format
     * @return string
     * @throws NotSupportedException if multi-column PrimaryKey is used
     */
    public function primaryKeySingle(): string
    {
        if (count(static::primaryKey()) === 1) {
            return static::primaryKey()[0];
        }
        throw new NotSupportedException('Not supported for multi-column primary keys');
    }

    /**
     * {@inheritdoc}
     * @param array<mixed, mixed> $link
     * @throws NotSupportedException
     */
    public function hasMany($class, $link = null)
    {
        if (empty($link)) {
            $link = [$this->primaryKeySingle() => $this->primaryKeySingle()];
        }
        return parent::hasMany($class, $link);
    }

    /**
     * {@inheritdoc}
     * @param array<mixed, mixed> $link
     * @throws NotSupportedException
     */
    public function hasOne($class, $link = null)
    {
        if (empty($link)) {
            $link = [$this->primaryKeySingle() => $this->primaryKeySingle()];
        }
        return parent::hasOne($class, $link);
    }

}