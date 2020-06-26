<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\interfaces\SettingInterface;

/**
 * Class Setting
 * @package app\modules\andmemasin\myabstract\src
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 * @property string $key
 * @property string $value
 */
class Setting extends MyActiveRecord implements SettingInterface
{

    /** @var string  */
    public $keyColumn = 'key';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [[$this->keyColumn], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 128],
            [[$this->keyColumn], 'unique']
        ]);
    }


    /**
     * @param $key string
     * @return static
     */
    public function findOneByKey($key){
        /** @var static $model */
        $model = static::find()
            ->andWhere([$this->keyColumn => $key])
            ->one();
        return $model;
    }

}
