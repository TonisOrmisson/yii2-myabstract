<?php

namespace andmemasin\myabstract\traits;
use yii\db\ActiveRecord;

/**
 * Trait ActiveRecordTestTrait
 * @package andmemasin\myabstract\traits
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 * @property ActiveRecord $model
 */
trait ActiveRecordTestTrait
{
    public function testAttributeLabelsForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->attributeLabels() as $key => $label) {
            $this->assertArrayHasKey($key, $this->model->attributes);
        }
    }
    public function testAttributeHintsForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->attributeHints() as $key => $label) {
            $this->assertArrayHasKey($key, $this->model->attributes);
        }
    }
    public function testRulesForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->rules() as $rule) {
            foreach ($rule[0] as $attribute) {
                $this->assertArrayHasKey($attribute, $this->model->attributes);
            }
        }
    }

}