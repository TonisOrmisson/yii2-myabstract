<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 16.10.17
 * Time: 23:39
 */

namespace andmemasin\myabstract;


use yii\db\ActiveRecord;

/**
 * Class Closing
 * @package andmemasin\myabstract
 *
 * @property string $last_closing_time
 * @property string $table_name
 */
class Closing extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{closing}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'last_closing_time'], 'required'],
            [['table_name'], 'string', 'max' => 128],
        ];
    }

}