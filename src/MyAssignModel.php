<?php
namespace andmemasin\myabstract;

use Yii;
use yii\db\ActiveRecordInterface;

/**
 * This is a base class for models that bind/assign child models to
 * parent models. Typically named as ParentHasChild pattern
 * @property bool $isAlreadyAssigned whether there is already a model with this parent-child relation
 */
class MyAssignModel extends MyActiveRecord
{

    /* @var $parentIdColumnName string Column name containing parent id FK */
    public string $parentIdColumnName;

    /* @var $childIdColumnName string Column name containing child id FK */
    public  string  $childIdColumnName;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [[$this->parentIdColumnName, $this->childIdColumnName], 'required'],
            [$this->childIdColumnName, function($attribute) {
                if ($this->isAlreadyAssigned) {
                    $this->addError($attribute, Yii::t('app', "Can only be used once!"));
                }
            }],
        ]);
    }

    public function assign(MyActiveRecord $parent, MyActiveRecord $child)
    {
        $this->{$this->parentIdColumnName} = $parent->primaryKey;
        $this->{$this->childIdColumnName} = $child->primaryKey;
    }

    public function getIsAlreadyAssigned() : bool
    {
        if ($this->isNewRecord) {
            $model = static::find()
                ->andWhere([$this->parentIdColumnName => $this->{$this->parentIdColumnName}])
                ->andWhere([$this->childIdColumnName => $this->{$this->childIdColumnName}])
                ->all();
            return !empty($model);
        }
        return false;
    }

}