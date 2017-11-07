<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 7.11.17
 * Time: 17:13
 */

namespace andmemasin\myabstract;

use yii\base\Model;

class StaticModel extends Model
{

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
}