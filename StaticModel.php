<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 7.11.17
 * Time: 17:13
 */

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
        $models = self::getModels();
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
        $arr = MyArrayHelper::indexByColumn(self::getModels(),self::$keyColumn);
        if(isset($arr[$key])){
            return new static($arr[$key]);
        }
        return null;
    }

}