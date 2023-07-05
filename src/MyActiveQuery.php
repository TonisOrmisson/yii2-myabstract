<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\interfaces\OnePrimaryKeyInterface;
use andmemasin\myabstract\traits\ModuleAwareTrait;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;

class MyActiveQuery extends ActiveQuery
{

    use ModuleAwareTrait;

    /**
     * @param $db
     * @return array<string, mixed>|ActiveRecordInterface|null
     */
    public function one($db = null) : array|ActiveRecordInterface|null
    {
        $this->singleItemQueryCaches();
        $this->limit(1);
        return parent::one($db);
    }

    /**
     * @param $db
     * @return array|\yii\db\ActiveRecordInterface[]
     */
    public function all($db = null) :array
    {
        $this->tableQueryCaches();
        return parent::all($db);
    }

    /**
     * @param $db
     * @return array<string, mixed>
     */
    public function column($db = null) : array
    {
        $this->tableQueryCaches();
        return parent::column($db);
    }

    public function count($q = '*', $db = null)
    {
        $this->tableQueryCaches();
        return parent::count($q, $db);
    }

    public function average($q, $db = null)
    {
        $this->tableQueryCaches();
        return parent::average($q, $db);
    }

    public function max($q, $db = null)
    {
        $this->tableQueryCaches();
        return parent::max($q, $db);
    }

    public function min($q, $db = null)
    {
        $this->tableQueryCaches();
        return parent::min($q, $db);
    }

    public function sum($q, $db = null)
    {
        $this->tableQueryCaches();
        return parent::sum($q, $db);
    }


    private function tableQueryCaches() : bool
    {
        if(method_exists($this->modelClass, 'primaryKeySingle')) {
            /** @var OnePrimaryKeyInterface $modelClass */
            $modelClass = $this->modelClass;
            $dependency = new TagDependency([
                'tags' => $modelClass::cahceDepencencyTagTable(),
                'reusable' => true,
            ]);
            $this->cache($this->cacheDuration(), $dependency);
            return true;
        }
        return false;

    }


    private function singleItemQueryCaches() : bool
    {
        if(!method_exists($this->modelClass, 'primaryKeySingle')) {
            return false;
        }

        /** @var OnePrimaryKeyInterface $modelClass */
        $modelClass = $this->modelClass;

        $primaryKeyFieldName = $modelClass::primaryKeySingle();
        if(!is_array($this->where)){
            return false;
        }
        $whereKeys = array_keys($this->where);
        if(count($this->where) === 1 and reset($whereKeys) === $primaryKeyFieldName) {
            $dependency = new TagDependency([
                'tags' => $modelClass::cahceDepencencyTagsOne($this->where[$primaryKeyFieldName]),
                'reusable' => true,
            ]);
            $this->cache($this->cacheDuration(), $dependency);
            return true;
        }
        $where = $this->prepare($this)->where;
        if(!is_array($where)) {
            return false;
        }

        foreach ($where as $condition) {

            if(!is_array($condition)) {
                continue;
            }

            if(isset($condition[0]) and $condition[0] === 'in') {
                if(
                    (is_string($condition[1]) and $condition[1] === $primaryKeyFieldName)
                    or (is_array($condition[1]) and reset($condition[1]) === $primaryKeyFieldName)
                ) {
                    if(count($condition[2]) === 1) {
                        $dependency = new TagDependency([
                            'tags' => $modelClass::cahceDepencencyTagsOne(current($condition[2])),
                            'reusable' => true,
                        ]);
                        $this->cache($this->cacheDuration(), $dependency);
                        return true;

                    }
                }

            }

            if(in_array($primaryKeyFieldName, array_keys($condition))){
                $dependency = new TagDependency([
                    'tags' => $modelClass::cahceDepencencyTagsOne($condition[$primaryKeyFieldName]),
                    'reusable' => true,
                ]);
                $this->cache($this->cacheDuration(), $dependency);
                return true;
            }

        }

        return false;

    }

    private function cacheDuration() : int
    {
        $duration = $this->getAbstractModule()->defaultCacheDuration;
        if(property_exists($this->modelClass, 'cacheDuration')) {
            if(is_int($this->modelClass::$cacheDuration)) {
                $duration = $this->modelClass::$cacheDuration;
            }
        }
        return $duration;
    }


}