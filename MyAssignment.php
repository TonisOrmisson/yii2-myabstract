<?php
/**
 * @link http://datuno.com/
 * @copyright Copyright (c) 2016 Andmemasin OÜ
 */

namespace andmemasin\myabstract;

use yii;
use yii\base\Model;
use yii\base\UserException;


/**
 * This is a model to manage assignments to ParentHasChildren type of
 * entities. To assign & delete children to and from parent entities
 *
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class MyAssignment  extends Model{

    /** @var integer[] $children_ids*/
    public $children_ids;

    /** @var MyActiveRecord[] */
    public $current_children;

    /** @var MyActiveRecord Last child by Time*/
    public $last_child;

    /** @var MyActiveRecord $parent */
    public $parent;

    /** @var MyActiveRecord $child */
    public $child;

    /** @var MyActiveRecord $assignment */
    public $assignment;

    /** @var string $child_fk_colname */
    public $child_fk_colname;

    /** @var string $parent_fk_colname */
    public $parent_fk_colname;

    /** @var string $assignmentClassname */
    public $assignmentClassname;

    /** @var string $order_colname IF assignments need to be ordered, set this name */
    public $order_colname;

    /** @var bool $isChildIdInteger Whether child id is integer (to clean from input string for order comparison) */
    public $isChildIdInteger;

    /** @var array items order (if are ordered) */
    public $itemsOrder;

    /** @var boolean Whether Assignments have separate children table or assigner directly to parents*/
    public $hasChildTable = true;

    const EVENT_BEFORE_ITEM_SAVE = 'beforeItemSave';

    /** @var  array array or attribute & value pairs that will be assigned to all created children [['attributeName1'=>'defaultValue1'],['attributeNamen'=>'defaultValuen]] */
    public $defaultValues;

    /** @inheritdoc */
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ITEM_SAVE, [$this, 'beforeItemSave']);

        if(!$this->parent){
            throw new yii\base\InvalidParamException('Parent not defined in '.self::className());
        }

        $this->setCurrentChildren();
        $this->itemsOrder = "";
        $this->assignmentClassname =  $this->assignment->className();
        parent::init();
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['parent','child','assignment'], 'required'],
            [['children_ids'], 'each','rule'=>[ 'string','max'=>16]],
            [['itemsOrder'], 'each','rule'=>[ 'integer']]
        ];

    }


    public function assignDefaultValues(){
        if(!empty($this->defaultValues)){
            foreach ($this->defaultValues as $attribute =>$value){
                $this->$attribute = $value;
            }
        }
    }

    /**
     * @param yii\base\Event $event has the Assignment child model attached as $event->data
     */
    public function beforeItemSave($event)
    {

    }

    public function save(){
        $i=0;
        $this->cleanChildrenIds();

        if(is_array($this->children_ids)){
            foreach ($this->children_ids as $childId){

                if(!$this->childExists($childId)){
                    $model = new $this->assignmentClassname;
                }else{
                    $model = $this->getCurrentChildById($childId);
                }


                $model->{$this->parent_fk_colname} = $this->parent->primaryKey;
                if($this->hasChildTable){
                    $model->{$this->child_fk_colname} = $childId;
                }
                $model->{$this->child_fk_colname} = $childId;


                // set order if order colname is set
                if($this->order_colname<>""){
                    $model->{$this->order_colname} = $i;
                }

                // assign default Value
                if(!empty($this->defaultValues)){
                    foreach ($this->defaultValues as $attribute =>$value){
                        $model->{$attribute} = $value;
                    }
                }
                // inject code before item save
                $event = new yii\base\Event();
                $event->data = $model;
                $this->trigger(self::EVENT_BEFORE_ITEM_SAVE,$event);

                if(!$model->save()){
                    $this->addErrors($model->errors);
                    return false;
                }
                $i++;
            }

        }

        // delete what was unselected
        if(is_array($this->current_children)){
            foreach ($this->current_children as $child){
                if((is_array($this->children_ids) && !in_array($child->{$this->child_fk_colname}, $this->children_ids))
                    or ( !is_array($this->children_ids))){

                    $child->delete();
                }
            }

        }
        $this->setCurrentChildren();

        return true;

    }

    private function hasOrderChanged(){
        return !($this->getCurrentChildrenIds() === $this->children_ids);
    }


    public function childExists($childId) {
        $currentChildrenIds = $this->getCurrentChildrenIds(false);
        if(is_array($currentChildrenIds)){
            return in_array($childId, $currentChildrenIds);
        }
        return false;
    }

    public function setCurrentChildren(){
        $query = $this->assignment->find()
            ->andWhere([$this->parent_fk_colname=>$this->parent->primaryKey]);

        // if order column is set, we order it ascending
        if($this->order_colname){
            $query->orderBy([$this->order_colname=>SORT_ASC]);
        }

        $children = $query->all();

        if ($children){
            $this->current_children = $children;
        }
        $this->getCurrentChildrenIds();

    }



    public function setLastChild(){
        $query = $this->assignment->find()
            ->andWhere([$this->parent_fk_colname => $this->parent->primaryKey]);
        $query->orderBy([
            $this->assignment->timeCreatedCol=>SORT_DESC,
            /**
             * if db does not record milliseconds, then we might have them
             * in the same second so we need to sort by id additionally
             */
            $this->assignment->primaryKey()[0]=>SORT_DESC
        ]);
        $this->last_child = $query->one();
    }

    /**
     * @param bool $set Whether we set the children_ids or not.
     * In case we get the id's before save - we do not want to set ids since we get
     * the ids externally (post)
     * @return array|bool
     */
    public function getCurrentChildrenIds($set = true) {
        if(is_array($this->current_children)){
            $ids = [];
            foreach ($this->current_children as $child){

                $ids[]=$child->{$this->child_fk_colname};
            }
            if($set){
                $this->children_ids = $ids;
            }
            return $ids;
        }
        return false;

    }
    private function getCurrentChildById($id) {
        if(is_array($this->current_children)){
            foreach ($this->current_children as $child){
                if($child->{$this->child_fk_colname} == $id){
                    return $child;
                }
            }
        }
        return false;
    }


    /**
     * Clean Ids to be integers
     */
    private function cleanChildrenIds() {
        if(is_array($this->children_ids) && $this->isChildIdInteger){
            $clean = [];
            foreach ($this->children_ids as $id) {
                $clean[] = intval($id);
            }
            $this->children_ids = $clean;
        }
    }


    /**
     * Get the last child assignment by TIME
     */
    public function getLastChild(){
        if(!$this->last_child){
            $this->setLastChild();
        }
        return $this->last_child;
    }
}