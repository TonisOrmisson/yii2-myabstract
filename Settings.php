<?php

namespace andmemasin\myabstract;

use andmemasin\surveyapp\models\SurveyLanguagesettingType;
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
                // only accept keys that are described in the model
                $type = SurveyLanguagesettingType::getByKey($key);
                if($type){
                    $this->{$key} = $setting->{$this->valueField};
                }
            }
        }
    }


    /**
     *
     */
    public function setSettings() {
        throw new yii\base\InvalidParamException('setSettings() needs to be overridden');
    }
}