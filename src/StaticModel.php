<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;
use yii\base\Model;
use andmemasin\helpers\MyArrayHelper;

class StaticModel extends Model
{
    public static string $keyColumn = 'key';

    use ConsoleAwareTrait;

    /**
     * {@inheritDoc}
     * @param array<string, mixed> $config
     */
    final function __construct($config = [])
    {
        parent::__construct($config);
    }

    
    /**
     * @return array<string|int, array<string, bool|float|int|resource|string|null>>
     */
    public function getModelAttributes() : array
    {
        return [];
    }

    /**
     * @return static[]
     */
    public function allModels()
    {
        $models = [];
        $data = $this->getModelAttributes();
        if (count($data)>0) {
            foreach ($data as $attributes) {
                $attributes['class'] = static::class;
                $models[] = \Yii::createObject($attributes);
            }
        }
        return $models;
    }

    public static function getById(int|string $id) : ?static
    {
        /** @var static $baseModel */
        $baseModel = \Yii::createObject(static::class);
        $modelsAttributes = $baseModel->getModelAttributes();
        if (isset($modelsAttributes[$id])) {
            $attributes = $modelsAttributes[$id];
            $attributes['class'] = static::class;
            /** @var static $model */
            $model = \Yii::createObject($attributes);
            return $model;
        }
        return null;
    }

    public static function getByKey(string $key) : ?static
    {
        /** @var static $model */
        $model = \Yii::createObject(static::class);
        $arr = MyArrayHelper::indexByColumn($model->getModelAttributes(), static::$keyColumn);

        if (isset($arr[$key])) {
            $attributes = $arr[$key];
            $attributes['class'] = static::class;
            /** @var static $model */
            $model = \Yii::createObject($attributes);
            return $model;
        }
        return null;
    }

}