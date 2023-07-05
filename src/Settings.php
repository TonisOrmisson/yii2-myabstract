<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\interfaces\TypeInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Model;

class Settings extends Model
{
    /** @var Setting[] */
    public array $settings = [];
    public string $itemClass = '';
    public string $typeRelationName = '';

    /** @var string Value field name in itemClass*/
    public string $valueField = 'value';

    /** @var boolean whether we skip checking attribute existence */
    public bool $doCheck = true;

    /** @var string a class name implementing TypeInterface  */
    public string $typeClass = '';

    /** @var string[] $alwaysSkipCheckAttributes */
    private static array $alwaysSkipCheckAttributes = ['typeClass', 'settings', 'itemClass', 'typeRelationName', 'valueField', 'doCheck', 'skipCheckAttributes'];


    /** @var string[] $skipCheckAttributes extended attributed that we skip in checking */
    public array $skipCheckAttributes = [];

    /**
     * {@inheritdoc}
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!$this->itemClass) {
            throw new InvalidArgumentException('ItemClass must be defined');
        }
        $this->checkSettings();

        $this->setSettings();
        if ($this->typeClass !== '') {
           $this->loadStrings();
        }
    }

    /**
     * Check if all defined attributes exist in settings[] and throw an error if its missing
     */
    protected function checkSettings() : void
    {
        if ($this->doCheck) {
            $skipAttributes = array_merge($this->skipCheckAttributes, self::$alwaysSkipCheckAttributes);
            $checkAttributes = array_diff(array_keys($this->attributes), $skipAttributes);

            if (!empty($checkAttributes)) {
                foreach ($checkAttributes as $checkAttribute) {
                    /** @var Setting $setting */
                    $setting = new $this->itemClass;

                    if (! $setting->findOneByKey($checkAttribute)) {
                        throw new InvalidConfigException('Key "' . $checkAttribute . '" is missing in ' . $this->itemClass);
                    }
                }
            }
        }

    }

    /** {@inheritdoc} */
    public function beforeValidate() {
        foreach ($this->attributes as $key => $value) {
            if ($value === "") {
                $this->$key = '';
            }
        }

        return parent::beforeValidate();
    }

    public function loadStrings() : void
    {
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


    public function setSettings() : void
    {
        // get existing settings
        /** @var Setting $settingClass */
        $settingClass = $this->itemClass;
        /** @var Setting[] $settings */
        $settings = $settingClass::find()->all();
        if(count($settings) === 0) {
            return;
        }
        foreach ($settings as $setting) {
            if (in_array($setting->{$setting->keyColumn}, array_keys($this->attributes))) {
                $this->settings[$setting->{$setting->keyColumn}] = $setting;
                $value = $setting->value;
                if($value == "") {
                    continue;
                }
                if(is_numeric($value)) {
                    if (is_integer(strpos($value,'.'))) {
                        $this->{$setting->{$setting->keyColumn}} = floatval($value);
                    } else {
                        $this->{$setting->{$setting->keyColumn}} = intval($value);
                    }

                } else {
                    $this->{$setting->{$setting->keyColumn}} = $value;
                }
            }
        }
    }

    /** {@inheritdoc}
     * @param array<string, mixed> $data
     */
    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName = null);
        if (!empty($this->settings)) {
            foreach ($this->settings as $setting) {
                if (in_array($setting->key, array_keys($this->attributes))) {
                    $setting->value = $this->{$setting->key};
                }
            }
        }
        return $result;
    }

    /**
     * @param bool $runValidation
     * @param ?string[] $attributeNames
     * @return bool
     */
    public function save(bool $runValidation = true, ?array $attributeNames = null) : bool
    {
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