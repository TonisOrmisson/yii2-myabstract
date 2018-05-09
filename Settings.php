<?php

namespace andmemasin\myabstract;

use andmemasin\surveyapp\models\SurveyLanguagesettingType;
use yii;

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

    /** @var boolean whether we skip checking attribute existence */
    public $doCheck = true;

    /** @var string[] $alwaysSkipCheckAttributes */
    private static $alwaysSkipCheckAttributes = ['settings', 'itemClass', 'typeRelationName', 'valueField', 'doCheck', 'skipCheckAttributes'];


    /** @var string[] $skipCheckAttributes extended attributed that we skip in checking */
    public $skipCheckAttributes = [];

    public function init()
    {
        parent::init();

        if (!$this->itemClass) {
            throw new yii\base\InvalidArgumentException('ItemClass must be defined');
        }
        $this->checkSettings();

        $this->setSettings();
        $this->loadStrings();
    }

    /**
     * Check if all defined attributes exist in settings[] and throw an error if its missing
     */
    protected function checkSettings() {
        if ($this->doCheck) {
            $skipAttributes = array_merge($this->skipCheckAttributes, self::$alwaysSkipCheckAttributes);
            $checkAttributes = array_diff(array_keys($this->attributes), $skipAttributes);

            if (!empty($checkAttributes)) {
                foreach ($checkAttributes as $checkAttribute) {
                    $class = $this->itemClass;

                    if (!$class::getByKey($checkAttribute)) {
                        throw new yii\base\InvalidConfigException('Key "' . $checkAttribute . '" is missing in ' . $class);
                    }
                }
            }
        }

    }

    public function beforeValidate() {
        foreach ($this->attributes as $key => $value) {
            if ($value === "") {
                $this->$key = NULL;
            }
        }

        return parent::beforeValidate();
    }

    public function loadStrings() {
        if (!empty($this->settings)) {
            foreach ($this->settings as $key => $setting) {
                // only accept keys that are described in the model
                $type = SurveyLanguagesettingType::getByKey($key);
                if ($type) {
                    $this->{$key} = $setting->{$this->valueField};
                }
            }
        }
    }


    /**
     *
     */
    public function setSettings() {
        throw new yii\base\InvalidParamException('setSettings() needs to be overriden');
    }
}