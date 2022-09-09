<?php

namespace andmemasin\myabstract\traits;

use andmemasin\myabstract\MyActiveRecord;
use yii\base\InvalidArgumentException;
use Yii;
use andmemasin\helpers\DateHelper;
use yii\base\UserException;
use yii\console\Application as ConsoleApplication;
use yii\db\ActiveRecord;
use yii\web\Application as WebApplication;
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
 *
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
trait MyActiveTrait
{

    /**
     * @var bool $is_logicDelete by default all deletes are logical deletes
     */
    public bool $is_logicDelete = true;


    // for updater & time & closer id
    public string $userCreatedCol = 'user_created';
    public string $userUpdatedCol = 'user_updated';
    public string $userClosedCol = 'user_closed';
    public string $timeCreatedCol = 'time_created';
    public string $timeUpdatedCol = 'time_updated';
    public string $timeClosedCol = 'time_closed';



    /**
     * {@inheritdoc}
     * @param ?array<int, string> $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null) : bool
    {
        $userId = $this->userId();
        $dateHelper = new DateHelper();
        if ($this->isNewRecord) {
            $this->{$this->timeClosedCol} = $dateHelper->getEndOfTime();
            $this->{$this->userCreatedCol} = $userId;
            $this->{$this->timeCreatedCol} = $dateHelper->getDatetime6();
        }
        $this->{$this->userUpdatedCol} = $userId;
        $this->{$this->timeUpdatedCol} = $dateHelper->getDatetime6();
        return parent::save($runValidation, $attributeNames);

    }



    /**
     * Get an User id for the record manipulation
     */
    private function userId() : int
    {
        $id = 1;
        $app = Yii::$app;
        if ($app instanceof ConsoleApplication) {
            return $id;
        }
        if (!isset(Yii::$app->user) || empty(Yii::$app->user->identity)) {
            return $id;
        }
        if ($app instanceof WebApplication) {
            $id = $app->user->getId();
            if (empty($id)) {
                $id = 1;
            }
        }
        return intval($id);

    }



    /**
     * Return a label for the model eg for display lists, selections
     * this method must be overridden
     */
    public function label() : string
    {
        return "";
    }

    /**
     * Get Model name for views.
     * This method needs to be overridden
     * @return string Model display name
     */
    public static function modelName() : string
    {
        return Inflector::camel2words(StringHelper::basename(self::tableName()));
    }

    /**
     * Override delete function to make it logical delete
     * {@inheritdoc}
     */
    public function delete()
    {
        if ($this->is_logicDelete) {
            return $this->logicalDelete();
        }
        return parent::delete();
    }

    private function logicalDelete() : int
    {
        $this->beforeDelete();
        // don't put new data if deleting
        $this->setAttributes($this->oldAttributes);

        // delete logically
        if ($this->userUpdatedCol) {
            $this->{$this->userUpdatedCol} = $this->userId();
        }
        if ($this->userClosedCol) {
            $this->{$this->userClosedCol} = $this->userId();
        }

        if ($this->timeUpdatedCol) {
            $this->{$this->timeUpdatedCol} = (new DateHelper())->getDatetime6();
        }

        if ($this->timeClosedCol) {
            $this->{$this->timeClosedCol} = (new DateHelper())->getDatetime6();
        }

        // don't validate on deleting
        if ($this->save(false)) {
            $this->afterDelete();
            return 1;
        }

        throw new UserException('Error deleting model');
    }


    /**
     * @param ActiveRecord[] $objects
     * @param array<string, string> $replaceParams
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function bulkCopy(array $objects, array $replaceParams)  : void
    {
        /**
         * @var ActiveRecord $model
         */
        $model = Yii::createObject(static::class);
        if (empty($objects)) {
            return;
        }

        $rows = [];
        $cols = [];
        foreach ($objects as $object) {
            if (empty($object->attributes)) {
                throw new InvalidArgumentException('Missing object attributes in ' . get_called_class() . ' ' . __FUNCTION__);
            }
            $row = $object->attributes;
            $cols = $model->attributes();
            foreach ($replaceParams as $key =>$value) {
                // remove primary keys (assuming auto-increment)
                foreach ($model->primaryKey() as $pk) {
                    unset($row[$pk]);
                }
                // remove pk fields from cols
                $cols = array_diff($cols, $model->primaryKey());
                $row[$key] = $value;
            }
            $rows[] = $row;

        }
        Yii::$app->db->createCommand()->batchInsert(parent::tableName(), $cols, $rows)->execute();

    }

    /**
     * Bulk delete (logic) objects based on the conditions set  in $params
     * NB! this does NOT call before/after delete
     * @param array<mixed, mixed> $params Array with the WHERE conditions as per QueryBuilder eg ['id'=>1] or.. ['>','id',3]
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function bulkDelete(array $params) : void
    {
        $dateHelper = new DateHelper();

        /**
         * @var MyActiveRecord
         */
        $model = Yii::createObject(static::class);

        if (empty($params)) {
            throw new InvalidArgumentException('No conditions defined for ' . get_called_class() . ' ' . __FUNCTION__);
        }


        $baseParams = [
            $model->timeClosedCol => $dateHelper->getDatetime6(),
            $model->userClosedCol => $model->userId(),
            $model->timeUpdatedCol=>$dateHelper->getDatetime6(),
            $model->userUpdatedCol =>$model->userId(),
        ];

        $conditions = [];
        $conditions[] = 'and';
        $conditions[] = ['is', static::tableName() . ".`" . $model->userClosedCol . '`', null];
        $conditions[] = $params;
        Yii::$app->db->createCommand()->update(parent::tableName(), $baseParams, $conditions)->execute();
    }


    /**
     * {@inheritdoc}
     * @return array<int, mixed>
     */
    public function rules()
    {
        return [
            [[$this->userCreatedCol, $this->userUpdatedCol, $this->userClosedCol], 'integer'],
            [[$this->timeCreatedCol, $this->timeUpdatedCol, $this->timeClosedCol], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     * @return array<string, string>
     */
    public function attributeLabels()
    {
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
    public static function find()
    {

        /** @var MyActiveRecord $child */
        $child = Yii::createObject(static::class);
        return parent::find()
            ->andWhere(['is', static::tableName() . ".`" . $child->userClosedCol . '`', null]);
    }

    /**
     * @param array<int, mixed> $filter
     * @return int
     * @deprecated @since 5.0.0
     */
    public static function getCount(array $filter = []) : int
    {
        $query = self::find();
        if (count($filter) === 0) {
            return intval($query->count());
        }

        return intval($query->andFilterWhere($filter)->count());
    }

    /**
     * a general query that adds the UserStrings filter on top of original query
     */
    public static function query() : Query
    {
        /** @var MyActiveRecord $child */
        $child = Yii::createObject(static::class);
        return (new Query())->andWhere(['is', static::tableName() . ".`" . $child->userClosedCol . '`', null]);
    }

    /**
     * Copy a model to a new model while replacing some params with new values
     * @param array<string, mixed> $map map of old model attribute as keys and new values as values
     * @throws yii\base\UserException
     */
    public static function copy(ActiveRecord $model, array $map): ?static
    {
        /** @var static $newModel */
        $newModel = Yii::createObject(static::class);
        $newModel->attributes = $model->attributes;
        foreach ($map as $key => $value) {
            $newModel->{$key} = $value;
        }
        if ($newModel->save()) {
            return $newModel;
        }
        throw new UserException('Error copying model');
    }


    public function getTimeCreated() : string
    {
        return $this->{$this->timeCreatedCol};
    }

    public function getTimeUpdated() : string
    {
        return $this->{$this->timeUpdatedCol};
    }

    public function getTimeClosed() : ?string
    {
        return $this->{$this->timeClosedCol};
    }

    /**
     * {@inheritDoc}
     * @return array|static[]
     */
    public static function all() : array
    {
        return parent::all();
    }


}
