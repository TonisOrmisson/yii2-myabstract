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

    public string $keyColumn = 'key';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [[$this->keyColumn], 'required'],
            [['value', 'key'], 'filter', 'filter'=>'strval'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 128],
            [[$this->keyColumn], 'unique'],
        ]);
    }


    public function findOneByKey(string $key) : ?static
    {
        /** @var static $model */
        $model = static::find()
            ->andWhere([$this->keyColumn => $key])
            ->limit(1)
            ->one();
        return $model;
    }


    public function getKey(): mixed
    {
        return $this->{$this->keyColumn};
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setKey(mixed $key) : void{
        $this->{$this->keyColumn} = $key;
    }
    public function setValue(mixed $value) : void {
        $this->value = strval($value);
    }

}
