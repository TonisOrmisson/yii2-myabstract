<?php
namespace andmemasin\myabstract;

/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 10.09.16
 * Time: 11:49
 */



use yii\db\ActiveRecord;
use andmemasin\myabstract\traits\MyActiveTrait;

class User extends ActiveRecord
{

    use MyActiveTrait{
        delete as myDelete;
    }

}