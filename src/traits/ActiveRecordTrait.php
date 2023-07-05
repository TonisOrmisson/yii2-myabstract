<?php

namespace andmemasin\myabstract\traits;

use andmemasin\surveyapp\models\Respondent;
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if(!$insert) {
            TagDependency::invalidate(\Yii::$app->getCache(), static::cahceDepencencyTagsOne($this->primaryKey));
        }

        TagDependency::invalidate(\Yii::$app->getCache(), static::cahceDepencencyTagTable());
    }


}