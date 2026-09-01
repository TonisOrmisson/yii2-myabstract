<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ConsoleAwareTrait;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

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
                /** @var array{class: class-string<static>, ...} $config */
                $config = ['class' => static::class] + $attributes;
                /** @var static $model */
                $model = \Yii::createObject($config);
                $models[] = $model;
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
            /** @var array{class: class-string<static>, ...} $config */
            $config = ['class' => static::class] + $attributes;
            /** @var static $model */
            $model = \Yii::createObject($config);
            return $model;
        }
        return null;
    }

    public static function getByKey(string $key) : ?static
    {
        /** @var static $model */
        $model = \Yii::createObject(static::class);
        $arr = ArrayHelper::index($model->getModelAttributes(), static function (array $row): string|int {
            $value = $row[static::$keyColumn] ?? throw new InvalidArgumentException('"' . static::$keyColumn . '" missing as key');
            return is_string($value) || is_int($value)
                ? $value
                : throw new InvalidArgumentException('"' . static::$keyColumn . '" value must be string or int');
        });

        if (isset($arr[$key])) {
            $attributes = $arr[$key];
            /** @var array{class: class-string<static>, ...} $config */
            $config = ['class' => static::class] + $attributes;
            /** @var static $model */
            $model = \Yii::createObject($config);
            return $model;
        }
        return null;
    }

}
