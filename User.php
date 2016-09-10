<?php
namespace andmemasin\myabstract;

/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 10.09.16
 * Time: 11:49
 */



use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    use MyActiveTrait;

    use MyActiveTrait{
        delete as myDelete;
    }

}