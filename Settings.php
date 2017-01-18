<?php

namespace andmemasin\myabstract;

use yii;
use yii\helpers\ArrayHelper;

class Settings extends yii\base\Model
{
    /** @var MyActiveRecord[] */
    private $settings;

    /** @var string */
    private $itemClass;

    /** @var string Key field name in itemClass*/
    private $keyField = 'key';

    /** @var string Value field name in itemClass*/
    private $valueField = 'value';

    public function init()
    {
        parent::init();

        if(!$this->itemClass){
            throw new yii\base\InvalidParamException('ItemClass must be defined');
        }

        $this->setSettings();
        $this->loadStrings();
    }

    public function beforeValidate() {
        foreach ($this->attributes as $key => $value){
            if ($value === ""){
                $this->$key = NULL;
            }
        }

        return parent::beforeValidate();
    }

    private function loadStrings() {
        foreach ($this->settings as $key => $setting) {
            // only accept keys that are described in the model
            if(in_array($key, array_keys($this->getAttributes()))){
                $this->{$key} = $setting->{$this->valueField};
            }
        }
    }


    /**
     *
     */
    private function setSettings() {
        $itemClass = $this->itemClass;
        $settings = $itemClass::find()
            ->all();
        $this->settings = ArrayHelper::index($settings, $this->keyField);
    }
}