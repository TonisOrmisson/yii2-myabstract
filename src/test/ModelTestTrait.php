<?php

namespace andmemasin\myabstract\test;
use yii\base\Model;

/**
 * Trait ModelTestTrait
 * @package andmemasin\myabstract\traits
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 * @property Model $model
 */
trait ModelTestTrait
{
    use InvokeProtectedTrait;

    public function testAttributeLabelsForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->attributeLabels() as $key => $label) {
            $this->assertArrayHasKey($key, array_merge(get_object_vars($this->model), $this->model->attributes));
        }
    }
    public function testAttributeHintsForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->attributeHints() as $key => $label) {
            $this->assertArrayHasKey($key, array_merge(get_object_vars($this->model), $this->model->attributes));
        }
    }
    public function testRulesForExistingAttributesOnly() {
        // labels only for actually existing attributes
        foreach ($this->model->rules() as $rule) {
            $rules = (is_array($rule[0]) ? $rule[0] : [$rule[0]] );
            foreach ($rules as $attribute) {
                $this->assertArrayHasKey($attribute, array_merge(get_object_vars($this->model), $this->model->attributes));
            }
        }
    }


}