<?php
namespace andmemasin\myabstract;


use yii\db\ActiveRecord;
use andmemasin\myabstract\traits\MyActiveTrait;

/**
 * Class User
 * @property string $username
 *
 * @package andmemasin\myabstract
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class User extends ActiveRecord
{

    use MyActiveTrait {
        delete as myDelete;
    }

}