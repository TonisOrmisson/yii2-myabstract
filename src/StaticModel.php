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
     * @return array
     */
    public function getModelAttributes() {
        return [];
    }

    /**
     * @return static[]
     */
    public function allModels()
    {
        $models = [];
        $data = $this->getModelAttributes();
        if (!empty($data)) {
            foreach ($data as $attributes) {
                $models[] = new static($attributes);
            }
        }
        return $models;
    }

    public static function getById(int|string $id) : ?static
    {
        $models = (new static)->getModelAttributes();
        if (isset($models[$id])) {
            return new static($models[$id]);
        }
        return null;
    }

    public static function getByKey(string $key) : ?static
    {
        $arr = MyArrayHelper::indexByColumn((new static)->getModelAttributes(), static::$keyColumn);

        if (isset($arr[$key])) {
            return new static($arr[$key]);
        }
        return null;
    }

}