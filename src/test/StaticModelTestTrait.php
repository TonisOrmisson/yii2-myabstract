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
    public function testAttributeLabelsForExistingAttributesOnly() : void
    {
        // labels only for actually existing attributes
        foreach ($this->model->attributeLabels() as $key => $label) {
            $this->assertArrayHasKey($key, $this->model->attributes);
        }
    }
    public function testAttributeHintsForExistingAttributesOnly() : void
    {
        // labels only for actually existing attributes
        foreach ($this->model->attributeHints() as $key => $label) {
            $this->assertArrayHasKey($key, $this->model->attributes);
        }
    }
    public function testRulesForExistingAttributesOnly() : void
    {
        // labels only for actually existing attributes
        foreach ($this->model->rules() as $rule) {
            foreach ($rule[0] as $attribute) {
                $this->assertArrayHasKey($attribute, $this->model->attributes);
            }
        }
    }


}