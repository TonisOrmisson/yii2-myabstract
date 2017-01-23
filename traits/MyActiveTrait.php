<?php
/**
 * @link http://datuno.com/
 * @copyright Copyright (c) 2016 Andmemasin OÜ
 */

namespace andmemasin\myabstract\traits;

use yii;
use andmemasin\helpers\DateHelper;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\db\Query;

/**
 * General code to be used in MyActiveRecord as well as User class
 * That can not extend MyActiveRecord
 *
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
trait MyActiveTrait {
    /**
     *
     * @var bool $is_logicDelete by default all deletes are logical deletes
     */
    public $is_logicDelete = true;

    // for updater & time & closer id
    public $userCreatedCol = 'user_created';
    public $userUpdatedCol = 'user_updated';
    public $userClosedCol = 'user_closed';
    public $timeCreatedCol = 'time_created';
    public $timeUpdatedCol = 'time_updated';
    public $timeClosedCol = 'time_closed';


    /**
     * Return a label for the model eg for display lists, selections
     * this method must be overriden
     * @return string
     */
    public function label() {
        return "";
    }

    /**
     * Get Model name for views.
     * This method needs to be overridden
     * @return string Model display name
     */
    public static function modelName()
    {
        // FIXME this is not OK
        return Inflector::camel2words(StringHelper::basename(self::tableName()));
    }

    /**
     * Override delete function to make it logical delete
     * @inheritdoc
     */
    public function delete() {
        // call beforeDelete event
        static::beforeDelete();
        if($this->is_logicDelete){

            // don't put new data if deleting
            $this->setAttributes($this->oldAttributes);

            // delete logically
            if($this->userUpdatedCol){
                $this->{$this->userUpdatedCol} =Yii::$app->user->identity->getId();
            }
            if($this->userClosedCol){
                $this->{$this->userClosedCol} =Yii::$app->user->identity->getId();
            }

            if($this->timeUpdatedCol){
                $this->{$this->timeUpdatedCol} =  DateHelper::getDatetime6();
            }

            if($this->timeClosedCol){
                $this->{$this->timeClosedCol} =  DateHelper::getDatetime6();
            }

            // don't validate on deleting
            if($this->save(false)){
                // call afterDelete event
                static::afterDelete();
                return true;
            }else {
                throw new yii\base\UserException('Error deleting model');
            }
        }else{
            // otherwise regular delete
            parent::delete();
        }
        return false;

    }


    public static function bulkCopy($objects,$replaceParams) {
        /**
         * @var yii\db\ActiveRecord $model
         */
        $model = new static;
        if(!empty($objects)){
            $rows = [];
            foreach ($objects as $object) {
                if(!empty($object->attributes)){
                    $row = $object->attributes;
                    $cols = $model->attributes();
                    foreach($replaceParams as $key =>$value){
                        // remove primary keys (assuming auto-increment)
                        foreach ($model->primaryKey() as $pk){
                            unset($row[$pk]);
                        }
                        // remove pk fields from cols
                        $cols = array_diff($cols, $model->primaryKey());
                        $row[$key] = $value;
                    }
                    $rows[]=$row;
                }  else {
                    throw new InvalidParamException('Missing object attributes in '. get_called_class().' '.__FUNCTION__);
                }

            }
            \Yii::$app->db->createCommand()->batchInsert(parent::tableName(), $cols, $rows)->execute();

        }
    }

    /**
     * Bulk delete (logic) objects based on the conditions set  in $params
     * @param array $params Array with the WHERE conditions as per QueryBuilder eg ['id'=>1] or.. ['>','id',3]
     * @throws InvalidParamException
     */
    public static function bulkDelete($params) {
        /**
         * @var \yii\db\ActiveRecord
         */
        $model = new static;
        if(!empty($params)){

            $baseParams = [
                $model->timeClosedCol=>DateHelper::getDatetime6(),
                $model->userClosedCol =>Yii::$app->user->identity->getId(),
                $model->timeUpdatedCol=>DateHelper::getDatetime6(),
                $model->userUpdatedCol =>Yii::$app->user->identity->getId(),
            ];

            $conditions = [];
            $conditions[] = 'and';
            $conditions[] = ['>',parent::tableName().".`".$model->timeClosedCol.'`',DateHelper::getDatetime6()];
            $conditions[] = $params;
            \Yii::$app->db->createCommand()->update(parent::tableName(), $baseParams,$conditions)->execute();

        }else{
            throw new InvalidParamException('No conditions defined for '. get_called_class().' '.__FUNCTION__);
        }



    }


    public function save($runValidation = true, $attributeNames = null) {
        // if there is no user Id, we use the default ID 1
        if(!isset(Yii::$app->user) || empty(Yii::$app->user->identity)){
            $userId = 1;
        }else{
            $userId = Yii::$app->user->identity->getId();
        }
        if ($this->getIsNewRecord()){
            $this->{$this->timeClosedCol} = DateHelper::getEndOfTime();
            $this->{$this->userCreatedCol} = $userId;
            $this->{$this->timeCreatedCol} = DateHelper::getDatetime6();
        }

        $this->{$this->userUpdatedCol} = $userId;
        $this->{$this->timeUpdatedCol} = DateHelper::getDatetime6();
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [[$this->userCreatedCol, $this->userUpdatedCol, $this->timeCreatedCol,$this->timeUpdatedCol, $this->timeClosedCol], 'required'],
            [[$this->userCreatedCol, $this->userUpdatedCol, $this->userClosedCol], 'integer'],
            [[$this->timeCreatedCol,$this->timeUpdatedCol,  $this->timeClosedCol], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            $this->userCreatedCol => Yii::t('app', 'Created by'),
            $this->userUpdatedCol => Yii::t('app', 'Updated by'),
            $this->userClosedCol => Yii::t('app', 'Closed by'),
            $this->timeCreatedCol => Yii::t('app', 'Created at'),
            $this->timeUpdatedCol => Yii::t('app', 'Updated at'),
            $this->timeClosedCol => Yii::t('app', 'Closed at'),
        ];
    }

    /**
     * Only returns models that have not been closed
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find() {
        $child = new static;
        $query =parent::find()
            ->andFilterWhere(['>',parent::tableName().".`".$child->timeClosedCol.'`',DateHelper::getDatetime6()]);
        return  $query;
    }


    public static function getCount($filter = null){
        $query = self::find();
        if($filter){
            $query->andFilterWhere($filter);
        }
        return $query->count();
    }

    /**
     * a general query that adds the UserStrings filter on top of original query
     * @return Query
     */
    public static function query() {
        $child = new static;
        return (new Query())->andFilterWhere(['>',parent::tableName().".`".$child->timeClosedCol.'`',DateHelper::getDatetime6()]);

    }

    /**
     * Copy a model to a new model while replacing some params with new values
     * @param \yii\db\ActiveRecord $model
     * @param array $map map of old model attribute as keys and new values as values
     * @return bool|static
     * @throws yii\base\UserException
     */
    public static function copy($model, $map){
        /**
         * @var \yii\db\ActiveRecord
         */
        $newModel = new static;
        $newModel->attributes = $model->attributes;
        foreach ($map as $key => $value) {
            $newModel->{$key} = $value;
        }
        if($newModel->save()){
            return $newModel;
        }else{
            throw new yii\base\UserException('Error copying model');
        }
    }


}
