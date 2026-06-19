<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\interfaces\OnePrimaryKeyInterface;
use andmemasin\myabstract\traits\ActiveRecordTrait;
use andmemasin\myabstract\traits\ConsoleAwareTrait;
use andmemasin\myabstract\traits\ModuleAwareTrait;
use yii\caching\Cache;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\base\InvalidConfigException;
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
    public static ?int $cacheDuration = null;


    /**
     * {@inheritdoc}
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $class
     * @param array<string, string>|null $link
     * @return ActiveQuery<T>
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
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $class
     * @param array<string, string>|null $link
     * @return ActiveQuery<T>
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
        if(static::usesCache() && static::$cacheAll) {
            $find->cache(0);
        }
        return $find;

    }

    public static function usesCache() : bool
    {
        $app = \Yii::$app;
        if ($app === null) {
            throw new InvalidConfigException('Yii application is not configured');
        }

        return ($app->get('cache', false) instanceof Cache and static::getDb()->enableQueryCache);
    }


}
