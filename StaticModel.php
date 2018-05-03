<?php

namespace andmemasin\myabstract;

use yii\base\Model;
use andmemasin\helpers\MyArrayHelper;

class StaticModel extends Model
{
    public static $keyColumn = 'key';

    /**
     * @return static[]
     */
    public static function getModels(){
        return [];
    }

    /**
     * @param $id
     * @return static
     */
    public static function getById($id){
        $models = static::getModels();
        if(isset($models[$id])){
            return new static($models[$id]);
        }
        return null;
    }
    /**
     * @param $key
     * @return static
     */
    public static function getByKey($key){
        $arr = MyArrayHelper::indexByColumn(static::getModels(),static::$keyColumn);

        if(isset($arr[$key])){
            return new static($arr[$key]);
        }
        return null;
    }

}