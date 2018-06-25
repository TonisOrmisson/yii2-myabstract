<?php

namespace andmemasin\myabstract;

use yii;

class Settings extends yii\base\Model
{
    /** @var Setting[] */
    public $settings;

    /** @var string */
    public $itemClass;

    /** @var string */
    public $typeRelationName;

    /** @var string Value field name in itemClass*/
    public $valueField = 'value';

    /** @var boolean whether we skip checking attribute existence */
    public $doCheck = true;

    /** @var string a class name implementing TypeInterface  */
    public $typeClass;

    /** @var string[] $alwaysSkipCheckAttributes */
    private static $alwaysSkipCheckAttributes = ['typeClass', 'settings', 'itemClass', 'typeRelationName', 'valueField', 'doCheck', 'skipCheckAttributes'];


    /** @var string[] $skipCheckAttributes extended attributed that we skip in checking */
    public $skipCheckAttributes = [];

    /** {@inheritdoc} */
    public function init()
    {
        parent::init();

        if (!$this->itemClass) {
            throw new yii\base\InvalidArgumentException('ItemClass must be defined');
        }
        $this->checkSettings();

        $this->setSettings();
        if (!is_null($this->typeClass)) {
            $this->loadStrings();
        }
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
                    /** @var Setting $setting */
                    $setting = new $this->itemClass;

                    if (! $setting->findOneByKey($checkAttribute)) {
                        throw new yii\base\InvalidConfigException('Key "' . $checkAttribute . '" is missing in ' . $this->itemClass);
                    }
                }
            }
        }

    }

    /** {@inheritdoc} */
    public function beforeValidate() {
        foreach ($this->attributes as $key => $value) {
            if ($value === "") {
                $this->$key = NULL;
            }
        }

        return parent::beforeValidate();
    }

    /** {@inheritdoc} */
    public function loadStrings() {
        if (!empty($this->settings)) {
            foreach ($this->settings as $key => $setting) {
                // only accept keys that are described in the model
                /** @var TypeInterface $typeClass */
                $typeClass = $this->typeClass;
                $type = $typeClass::getByKey($key);
                if ($type) {
                    $this->{$key} = $setting->{$this->valueField};
                }
            }
        }
    }


    /** {@inheritdoc} */
    public function setSettings() {
        // get existing settings

        /** @var Setting $settingClass */
        $settingClass = $this->itemClass;
        $query = $settingClass::find();
        $settings = $query->all();
        if (!empty($settings)) {
            foreach ($settings as $setting) {
                if (in_array($setting->{$setting->keyColumn}, array_keys($this->attributes))) {
                    $this->settings[$setting->{$setting->keyColumn}] = $setting;
                    $this->{$setting->{$setting->keyColumn}} = $setting->value;
                }
            }
        }
    }

    /** {@inheritdoc} */
    public function load($data, $formName = null)
    {
        parent::load($data, $formName = null);
        if (!empty($this->settings)) {
            foreach ($this->settings as $setting) {
                if (in_array($setting->key, array_keys($this->attributes))) {
                    $setting->value = $this->{$setting->key};
                }
            }
        }
    }

    /** {@inheritdoc} */
    public function save() {
        if (!empty($this->settings)) {
            foreach ($this->settings as $key=> $setting) {
                if (in_array($setting->key, array_keys($this->attributes))) {
                    $setting->save();
                    $this->settings[$key] = $setting;
                    $this->addErrors($setting->errors);
                }
            }
        }
        return empty($this->errors);
    }

}