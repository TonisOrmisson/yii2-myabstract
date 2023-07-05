<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\interfaces\OnePrimaryKeyInterface;
use andmemasin\myabstract\traits\ActiveRecordTrait;
use andmemasin\myabstract\traits\ConsoleAwareTrait;
use andmemasin\myabstract\traits\ModuleAwareTrait;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\base\NotSupportedException;

/**
 * Class ActiveRecord
 * @package andmemasin\myabstract
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class ActiveRecord extends BaseActiveRecord implements OnePrimaryKeyInterface
{
    use ConsoleAwareTrait;
    use ActiveRecordTrait;
    use ModuleAwareTrait;

    public static bool $cacheAll = false;
    public bool $isSearchModel = false;
    public static bool $useQueryCache = true;
    public static ?int $cacheDuration = null;


    /**
     * {@inheritdoc}
     * @param array<mixed, mixed> $link
     * @throws NotSupportedException
     */
    public function hasMany($class, $link = null) : ActiveQuery
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
    public function hasOne($class, $link = null) : ActiveQuery
    {
        if (empty($link)) {
            $link = [$this->primaryKeySingle() => $this->primaryKeySingle()];
        }
        return parent::hasOne($class, $link);
    }

    public static function find()
    {
        $find = parent::find();
        if(static::$cacheAll) {
            $find->cache(0);
        }
        return $find;

    }

}