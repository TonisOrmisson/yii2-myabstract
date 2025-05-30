<?php

namespace andmemasin\myabstract\traits;

use yii\base\NotSupportedException;
use yii\caching\TagDependency;

trait ActiveRecordTrait
{
    public static function primaryKeySingle(): string
    {
        if (count(static::primaryKey()) === 1) {
            return static::primaryKey()[0];
        }
        throw new NotSupportedException('Not supported for multi-column primary keys');
    }

    public static function cahceDepencencyTagsOne(int|string $primaryKey) : array
    {
        return [static::class."::$primaryKey"];
    }

    public static function cahceDepencencyTagTable() : array
    {
        return [static::tableName()];
    }

    /**
     * @param bool $insert
     * @param array<string, mixed> $changedAttributes
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function afterSave($insert, $changedAttributes) : void
    {
        parent::afterSave($insert, $changedAttributes);
        if(!$insert) {
            if((is_int($this->primaryKey) or is_string($this->primaryKey)) === false) {
                throw new \Exception("invalid type for primaryKey value");
            }
            if(static::usesCache()) {
                $cache = $this->getCache();
                if($cache !== null) {
                    TagDependency::invalidate($cache, static::cahceDepencencyTagsOne($this->primaryKey));
                }
            }
        }

        if(static::usesCache()) {
            $cache = $this->getCache();
            if(static::$cacheAll) {
                // if something cached(all) changes, we flush all caches
                $cache?->flush();
            }
            if($cache !== null) {
                TagDependency::invalidate($cache, static::cahceDepencencyTagTable());
            }
        }
    }


}