<?php

namespace andmemasin\myabstract;

use yii;
use yii\helpers\ArrayHelper;

class Settings extends yii\base\Model
{
    /** @var MyActiveRecord[] */
    public $settings;

    /** @var string */
    public $itemClass;

    /** @var string */
    public $typeRelationName;


    /** @var string Value field name in itemClass*/
    public $valueField = 'value';

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

    public function loadStrings() {
        if(!empty($this->settings)){
            foreach ($this->settings as $key => $setting) {
                $this->{$key} = $setting->{$this->valueField};
            }
        }
    }

    public function save() {

        foreach ($this->settings as $key => $setting) {
            // update only what's changed
            if(in_array($key, array_keys($this->getAttributes()))){
                if ($setting->value <> $this->{$key}) {
                    $setting->value = $this->{$key};
                    $setting->save();
                    $this->addErrors($setting->errors);
                }
            }
        }
        if($this->errors){
            return false;
        }
        return true;
    }


    /**
     *
     */
    public function setSettings() {
        throw new yii\base\InvalidParamException('setSettings() needs to be overridden');
    }
}