<?php
namespace andmemasin\myabstract;


use yii\db\ActiveRecord;
use andmemasin\myabstract\traits\MyActiveTrait;

class User extends ActiveRecord
{

    use MyActiveTrait{
        delete as myDelete;
    }

}