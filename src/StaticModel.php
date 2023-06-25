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