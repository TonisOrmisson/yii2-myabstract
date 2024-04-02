<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\exceptions\MyAbstractException;
use andmemasin\myabstract\interfaces\OnePrimaryKeyInterface;
use andmemasin\myabstract\traits\ModuleAwareTrait;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;

class MyActiveQuery extends ActiveQuery
{

    use ModuleAwareTrait;

    /** @var TagDependency[] $dependencies all set dependendies */
    private array $dependencies = [];
    private bool $viaTableOK = false;


    /**
     * @param $db
     * @return array<string, mixed>|ActiveRecordInterface|null
     */
    public function one($db = null) : array|ActiveRecordInterface|null
    {
        $this->limit(1);
        if(!$this->abstractModule->useCache) {
            return parent::one($db);
        }
        $this->singleItemQueryCaches();
        $result = parent::one($db);
        if($result === null) {
            $this->cleanAllDependencies();
        }
        return $result;
    }

    /**
     * @param $db
     * @return array|\yii\db\ActiveRecordInterface[]
     */
    public function all($db = null) :array
    {
        if(!$this->abstractModule->useCache) {
            return parent::all($db);
        }
        $this->tableQueryCaches();
        return parent::all($db);
    }

    /**
     * @param $db
     * @return array<string, mixed>
     */
    public function column($db = null) : array
    {
        if(!$this->abstractModule->useCache) {
            return parent::column($db);
        }

        $this->tableQueryCaches();
        return parent::column($db);
    }

    public function count($q = '*', $db = null)
    {
        if(!$this->abstractModule->useCache) {
            return parent::count($q, $db);
        }
        $this->tableQueryCaches();
        return parent::count($q, $db);
    }

    public function average($q, $db = null)
    {
        if(!$this->abstractModule->useCache) {
            return parent::average($q, $db);
        }
        $this->tableQueryCaches();
        return parent::average($q, $db);
    }

    public function max($q, $db = null)
    {
        if(!$this->abstractModule->useCache) {
            return parent::max($q, $db);
        }
        $this->tableQueryCaches();
        return parent::max($q, $db);
    }

    public function min($q, $db = null)
    {
        if(!$this->abstractModule->useCache) {
            return parent::min($q, $db);
        }
        $this->tableQueryCaches();
        return parent::min($q, $db);
    }

    public function sum($q, $db = null)
    {
        if(!$this->abstractModule->useCache) {
            return parent::sum($q, $db);
        }
        $this->tableQueryCaches();
        return parent::sum($q, $db);
    }

    public function setViaTableOK() : self
    {
        $this->viaTableOK = true;
        return $this;
    }

    /**
     * @param array<string,string> $link
     */
    public function viaTable($tableName, $link, ?callable $callable = null) : self
    {

        /** @var ?\yii\db\ActiveRecord $primaryModel */
        $primaryModel = $this->primaryModel;
        $modelClass = $primaryModel ? get_class($this->primaryModel) : $this->modelClass;
        /** @var ActiveRecord $relationModel */
        $relationModel = \Yii::createObject($modelClass);

        if(($relationModel instanceof  MyActiveRecord) && $relationModel->is_logicDelete && !$this->viaTableOK) {
            throw new MyAbstractException("please check that ViaTable also includes the ->timeClosedCondition() inside the relation query! 
            IF it is checked then also set this->setViaTableOK() to pass this exception");
        }
        return parent::viaTable($tableName, $link, $callable);

    }


    private function cleanAllDependencies() : void
    {
        foreach ($this->dependencies as $dependency) {
            TagDependency::invalidate($this->getCache(), $dependency->tags);
        }
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
            $this->dependencies[] = $dependency;
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

        $where = $this->prepare($this)->where;


        if(!is_array($where)){
            return false;
        }
        $whereKeys = array_keys($where);
        if(count($where) === 1 and reset($whereKeys) === $primaryKeyFieldName) {
            $dependency = new TagDependency([
                'tags' => $modelClass::cahceDepencencyTagsOne($where[$primaryKeyFieldName]),
                'reusable' => true,
            ]);
            $this->dependencies[] = $dependency;
            $this->cache($this->cacheDuration(), $dependency);
            return true;
        }


        if(count($where) > 2 and $where[0] == 'and'
            and is_array($where[1]) and isset($where[1][0])
            and is_string($where[1][0])
            and $where[1][0] === 'or'
            and str_contains(serialize($where[1][1]), 'user_closed')
            and is_array($where[2])) {


            $where2Keys = array_keys($where[2]);
            $where2Key = reset($where2Keys);

            if($where2Key === $primaryKeyFieldName) {
                $dependency = new TagDependency([
                    'tags' => $modelClass::cahceDepencencyTagsOne($where[2][$where2Key]),
                    'reusable' => true,
                ]);
                $this->dependencies[] = $dependency;
                $this->cache($this->cacheDuration(), $dependency);
                return true;
            }
        }

        // single item query with "in": primaryKeyField in(primaryKey)
        if(count($where) > 2 and $where[0] == 'in'
            and is_array($where[1]) and count($where[1]) === 1 and isset($where[1][0]) and $where[1][0] === $primaryKeyFieldName
            and is_array($where[2]) and count($where[2]) === 1
        ){
            $where2Keys = array_keys($where[2]);
            $where2Key = reset($where2Keys);
            $dependency = new TagDependency([
                'tags' => $modelClass::cahceDepencencyTagsOne($where[2][$where2Key]),
                'reusable' => true,
            ]);
            $this->dependencies[] = $dependency;
            $this->cache($this->cacheDuration(), $dependency);
            return true;

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
                        $this->dependencies[] = $dependency;
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
                $this->dependencies[] = $dependency;
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