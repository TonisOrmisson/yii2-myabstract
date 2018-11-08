<?php

namespace andmemasin\myabstract;

use yii\base\Model;
use andmemasin\helpers\MyArrayHelper;

class StaticModel extends Model
{
    public static $keyColumn = 'key';

    /**
     * @return array
     */
    public function getModelAttributes() {
        return [];
    }

    /**
     * @param $id
     * @return static
     */
    public static function getById($id) {
        $models = (new static)->getModelAttributes();
        if (isset($models[$id])) {
            return new static($models[$id]);
        }
        return null;
    }
    /**
     * @param $key
     * @return static
     */
    public static function getByKey($key) {
        $arr = MyArrayHelper::indexByColumn((new static)->getModelAttributes(), static::$keyColumn);

        if (isset($arr[$key])) {
            return new static($arr[$key]);
        }
        return null;
    }

}