<?php

namespace andmemasin\myabstract;

/**
 * Class Setting
 * @package app\modules\andmemasin\myabstract\src
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 * @property string $key
 * @property string $value
 */
class Setting extends MyActiveRecord
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
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge([
            'actionlog' => [
                'class' => 'andmemasin\actionlog\behaviors\ActionLogBehavior',
            ],
        ], parent::behaviors());
    }

    /**
     * @param $key string
     * @return static
     */
    public function findOneByKey($key){
        $model = static::find()
            ->andWhere([$this->keyColumn => $key])
            ->one();
        return $model;
    }

}