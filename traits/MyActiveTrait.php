<?php

namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\Closing;
use yii;
use andmemasin\helpers\DateHelper;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\db\Query;

/**
 * General code to be used in MyActiveRecord as well as User class
 * That can not extend MyActiveRecord
 *
 * @property string $timeCreated
 * @property string $timeUpdated
 * @property string $timeClosed
 * @property DateHelper $dateHelper
 *
 * @package andmemasin\myabstract
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
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $userId = $this->userId();
        if ($this->isNewRecord) {
            $this->{$this->timeClosedCol} = $this->dateHelper->getEndOfTime();
            $this->{$this->userCreatedCol} = $userId;
            $this->{$this->timeCreatedCol} = $this->dateHelper->getDatetime6();
        }

        $this->{$this->userUpdatedCol} = $userId;
        $this->{$this->timeUpdatedCol} = $this->dateHelper->getDatetime6();
        return parent::save($runValidation, $attributeNames);

    }

    /**
     * Get an user id for the record manipulation
     * @return integer
     */
    private function userId()
    {
        if (Yii::$app instanceof yii\console\Application) {
            return 1;
        } else {
            if (!isset(Yii::$app->user) || empty(Yii::$app->user->identity)) {
                return 1;
            } else {
                return (int) Yii::$app->user->identity->getId();
            }
        }
    }


    /**
     * Return a label for the model eg for display lists, selections
     * this method must be overridden
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
     * {@inheritdoc}
     */
    public function delete() {
        if ($this->is_logicDelete) {
            $this->beforeDelete();
            // don't put new data if deleting
            $this->setAttributes($this->oldAttributes);

            // delete logically
            if ($this->userUpdatedCol) {
                $this->{$this->userUpdatedCol} = Yii::$app->user->identity->getId();
            }
            if ($this->userClosedCol) {
                $this->{$this->userClosedCol} = Yii::$app->user->identity->getId();
            }

            if ($this->timeUpdatedCol) {
                $this->{$this->timeUpdatedCol} = $this->dateHelper->getDatetime6();
            }

            if ($this->timeClosedCol) {
                $this->{$this->timeClosedCol} = $this->dateHelper->getDatetime6();
            }

            // don't validate on deleting
            if ($this->save(false)) {
                self::updateClosingTime(static::tableName());
                $this->afterDelete();
                return true;
            } else {
                throw new yii\base\UserException('Error deleting model');
            }

        } else {
            // otherwise regular delete
            parent::delete();
            return true;
        }

    }


    public static function bulkCopy($objects, $replaceParams) {
        /**
         * @var yii\db\ActiveRecord $model
         */
        $model = new static;
        if (!empty($objects)) {
            $rows = [];
            $cols = [];
            foreach ($objects as $object) {
                if (!empty($object->attributes)) {
                    $row = $object->attributes;
                    $cols = $model->attributes();
                    foreach ($replaceParams as $key =>$value) {
                        // remove primary keys (assuming auto-increment)
                        foreach ($model->primaryKey as $pk) {
                            unset($row[$pk]);
                        }
                        // remove pk fields from cols
                        $cols = array_diff($cols, $model->primaryKey);
                        $row[$key] = $value;
                    }
                    $rows[] = $row;
                } else {
                    throw new yii\base\InvalidArgumentException('Missing object attributes in ' . get_called_class() . ' ' . __FUNCTION__);
                }

            }
            \Yii::$app->db->createCommand()->batchInsert(parent::tableName(), $cols, $rows)->execute();

        }
    }

    /**
     * Bulk delete (logic) objects based on the conditions set  in $params
     * NB! this does NOT call before/after delete
     * @param array $params Array with the WHERE conditions as per QueryBuilder eg ['id'=>1] or.. ['>','id',3]
     */
    public static function bulkDelete($params) {
        $dateHelper = new DateHelper();

        /**
         * @var \yii\db\ActiveRecord
         */
        $model = new static;
        if (!empty($params)) {

            $baseParams = [
                $model->timeClosedCol=>$dateHelper->getDatetime6(),
                $model->userClosedCol =>Yii::$app->user->identity->getId(),
                $model->timeUpdatedCol=>$dateHelper->getDatetime6(),
                $model->userUpdatedCol =>Yii::$app->user->identity->getId(),
            ];

            $conditions = [];
            $conditions[] = 'and';
            $conditions[] = ['>', static::tableName() . ".`" . $model->timeClosedCol . '`', $dateHelper->getDatetime6()];
            $conditions[] = $params;
            \Yii::$app->db->createCommand()->update(parent::tableName(), $baseParams, $conditions)->execute();
            self::updateClosingTime(static::tableName());

        } else {
            throw new yii\base\InvalidArgumentException('No conditions defined for ' . get_called_class() . ' ' . __FUNCTION__);
        }



    }


    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [[$this->userCreatedCol, $this->userUpdatedCol, $this->timeCreatedCol, $this->timeUpdatedCol, $this->timeClosedCol], 'required'],
            [[$this->userCreatedCol, $this->userUpdatedCol, $this->userClosedCol], 'integer'],
            [[$this->timeCreatedCol, $this->timeUpdatedCol, $this->timeClosedCol], 'safe'],
        ];
    }
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find() {
        $child = new static;
        $query = parent::find()
            ->andFilterWhere($child->timeClosedCondition());
        return  $query;
    }

    public function timeClosedCondition()
    {
        $lastClosingTime = self::lastClosingTime(static::tableName());
        return ['>', static::tableName() . ".`" . $this->timeClosedCol . '`', $lastClosingTime];
    }


    public static function getCount($filter = null) {
        $query = self::find();
        if ($filter) {
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
        $dateHelper = new DateHelper();
        return (new Query())->andFilterWhere(['>', parent::tableName() . ".`" . $child->timeClosedCol . '`', $dateHelper->getDatetime6()]);
    }

    /**
     * Copy a model to a new model while replacing some params with new values
     * @param \yii\db\ActiveRecord $model
     * @param array $map map of old model attribute as keys and new values as values
     * @return bool|static
     * @throws yii\base\UserException
     */
    public static function copy($model, $map) {
        /**
         * @var \yii\db\ActiveRecord
         */
        $newModel = new static;
        $newModel->attributes = $model->attributes;
        foreach ($map as $key => $value) {
            $newModel->{$key} = $value;
        }
        if ($newModel->save()) {
            return $newModel;
        } else {
            throw new yii\base\UserException('Error copying model');
        }
    }

    /**
     * @param string $tableName
     * @return mixed|string
     */
    private static function lastClosingTime($tableName) {
        $dateHelper = new DateHelper();

        if (!self::hasClosing($tableName)) {
            self::createClosingRow($tableName);
        }
        /** @var Closing $closing */
        $closing = Closing::findOne($tableName);
        if ($closing) {
            return $closing->last_closing_time;
        }
        return $dateHelper->getDatetime6();
    }

    /**
     * @param string $tableName
     * @return bool
     */
    private static function hasClosing($tableName){
            $closing = Closing::findOne($tableName);
            return !($closing == null);
    }

    /**
     * @param $tableName
     * @return Closing
     */
    private static function createClosingRow($tableName) {

        if (!self::hasClosing($tableName)) {
            $dateHelper = new DateHelper();
            $closing = new Closing([
                'table_name'=>$tableName,
                'last_closing_time' => $dateHelper->getDatetime6(),
            ]);
            $closing->save();
            return $closing;
        }
        return null;
    }

    private static function updateClosingTime($tableName) {
        if (!self::hasClosing($tableName)) {
            self::createClosingRow($tableName);
        }
        /** @var Closing $closing */
        $closing = Closing::findOne($tableName);
        $dateHelper = new DateHelper();
        $closing->last_closing_time = $dateHelper->getDatetime6();
        $closing->save();
    }

    /**
     * @return string
     */
    public function getTimeCreated()
    {
        return $this->{$this->timeCreatedCol};
    }

    /**
     * @return string
     */
    public function getTimeUpdated()
    {
        return $this->{$this->timeUpdatedCol};
    }

    /**
     * @return string
     */
    public function getTimeClosed()
    {
        return $this->{$this->timeClosedCol};
    }

    /**
     * @return DateHelper
     */
    public function getDateHelper()
    {
        return new DateHelper();
    }


}
