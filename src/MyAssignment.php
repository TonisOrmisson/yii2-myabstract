<?php

namespace andmemasin\myabstract;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use andmemasin\myabstract\events\MyAssignmentEvent;
use yii\db\ActiveRecordInterface;

/**
 * This is a model to manage assignments to ParentHasChildren type of
 * entities. To assign & delete children to and from parent entities
 *
 * @property MyActiveRecord $lastChild @deprecated
 * @package app\models\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 * {@inheritdoc}
 */
class MyAssignment  extends Model
{

    /** @var integer[] $children_ids*/
    public array|string $children_ids = [];

    /** @var ActiveRecordInterface[] indexed by child PK */
    public array|string $current_children = [];

    /** @var ActiveRecordInterface Last child by Time*/
    public ActiveRecordInterface $last_child;

    public ActiveRecord $parent;
    public MyActiveRecord $child;
    public MyActiveRecord $assignment;

    /** @var ActiveRecordInterface $assignmentItem The Assignment item we process at the moment */
    public ActiveRecordInterface $assignmentItem;

    public string $child_fk_colname = '';
    public string $parent_fk_colname = '';
    public string $assignmentClassname = '';

    /** @var string $order_colname IF assignments need to be ordered, set this name */
    public string $order_colname = '';

    /** @var bool $isChildIdInteger Whether child id is integer (to clean from input string for order comparison) */
    public bool $isChildIdInteger = true;

    /** @var array items order (if are ordered) */
    public array $itemsOrder = [];

    /** @var boolean Whether Assignments have separate children table or assigner directly to parents*/
    public bool $hasChildTable = true;

    const EVENT_BEFORE_ITEM_SAVE = 'beforeItemSave';

    /** @var  array array or attribute & value pairs that will be assigned to all created children [['attributeName1'=>'defaultValue1'],['attributeNameN'=>'defaultValueN]] */
    public array $defaultValues = [];

    /** {@inheritdoc} */
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ITEM_SAVE, [$this, 'beforeItemSave']);

        if (!$this->parent) {
            throw new InvalidArgumentException('Parent not defined in ' . self::class);
        }

        $this->setCurrentChildren();
        $this->itemsOrder = [];
        $this->assignmentClassname = get_class($this->assignment);
        parent::init();
    }

    /** {@inheritdoc} */
    public function rules()
    {
        return [
            [['parent', 'child', 'assignment'], 'required'],
            [['children_ids'], 'each', 'rule'=>['string', 'max'=>16]],
            [['itemsOrder'], 'each', 'rule'=>['integer']]
        ];

    }


    public function assignDefaultValues() {
        if (!empty($this->defaultValues)) {
            foreach ($this->defaultValues as $attribute =>$value) {
                $this->$attribute = $value;
            }
        }
    }

    /**
     * @param MyAssignmentEvent $event
     */
    public function beforeItemSave($event)
    {

    }

    public function save() {
        $i = 0;
        $this->cleanChildrenIds();

        if (is_array($this->children_ids)) {
            foreach ($this->children_ids as $childId) {

                if (!$this->childExists($childId)) {
                    $model = new $this->assignmentClassname;
                } else {
                    $model = $this->getCurrentChildById($childId);
                }


                $model->{$this->parent_fk_colname} = $this->parent->primaryKey;
                if ($this->hasChildTable) {
                    $model->{$this->child_fk_colname} = $childId;
                }
                $model->{$this->child_fk_colname} = $childId;


                // set order if order colname is set
                if ($this->order_colname <> "") {
                    $model->{$this->order_colname} = $i;
                }

                // assign default Value
                if (!empty($this->defaultValues)) {
                    foreach ($this->defaultValues as $attribute =>$value) {
                        $model->{$attribute} = $value;
                    }
                }
                // inject code before item save
                $this->assignmentItem = $model;
                $event = new MyAssignmentEvent;
                $event->item = $model;
                $this->trigger(self::EVENT_BEFORE_ITEM_SAVE, $event);

                if (!$this->assignmentItem->save()) {
                    $this->addErrors($this->assignmentItem->errors);
                    return false;
                }
                $i++;
            }

        }

        // delete what was unselected
        if (is_array($this->current_children)) {
            foreach ($this->current_children as $child) {
                if ((is_array($this->children_ids) && !in_array($child->{$this->child_fk_colname}, $this->children_ids))
                    or (!is_array($this->children_ids))) {

                    $child->delete();
                }
            }

        }
        $this->setCurrentChildren();

        return true;

    }


    public function childExists($childId) {
        $currentChildrenIds = $this->getCurrentChildrenIds(false);
        if (is_array($currentChildrenIds)) {
            return in_array($childId, $currentChildrenIds);
        }
        return false;
    }

    public function setCurrentChildren() {
        $query = $this->identifyChildrenQuery();

        // if order column is set, we order it ascending
        if ($this->order_colname) {
            $query->orderBy([$this->order_colname=>SORT_ASC]);
        }
        $indexCol = (empty($this->child_fk_colname) ? $this->child->primaryKeySingle() : $this->child_fk_colname);
        $children = $query->indexBy($indexCol)->all();
        $this->current_children = $children;
        $this->getCurrentChildrenIds();

    }



    public function setLastChild() {
        $indexCol = (empty($this->child_fk_colname) ? $this->child->primaryKeySingle() : $this->child_fk_colname);
        $query = $this->identifyChildrenQuery();
        $query->orderBy([
            $this->assignment->timeCreatedCol => SORT_DESC,
            /**
             * if db does not record milliseconds, then we might have them
             * in the same second so we need to sort by id additionally
             */
            $this->assignment->{$indexCol} => SORT_DESC
        ]);
        /** @var MyActiveRecord $model */
        $model = $query->limit(1)->one();
        $this->last_child = $model;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function identifyChildrenQuery() {
        $query = $this->assignment->find()
            ->andWhere([$this->parent_fk_colname => $this->parent->primaryKey]);
        return $query;

    }

    /**
     * @param bool $set Whether we set the children_ids or not.
     * In case we get the id's before save - we do not want to set ids since we get
     * the ids externally (post)
     * @return array|bool
     */
    public function getCurrentChildrenIds($set = true) {
        if (is_array($this->current_children)) {
            $ids = [];
            foreach ($this->current_children as $child) {

                $ids[] = $child->{$this->child_fk_colname};
            }
            if ($set) {
                $this->children_ids = $ids;
            }
            return $ids;
        }
        return false;

    }
    private function getCurrentChildById($id) {
        if (is_array($this->current_children)) {
            foreach ($this->current_children as $child) {
                if ($child->{$this->child_fk_colname} == $id) {
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
        if (is_array($this->children_ids) && $this->isChildIdInteger) {
            $clean = [];
            foreach ($this->children_ids as $id) {
                $clean[] = intval($id);
            }
            $this->children_ids = $clean;
        }
    }


    /**
     * Get the last child assignment by TIME
     * @return MyActiveRecord
     * @deprecated
     */
    public function getLastChild() {
        if (!$this->last_child) {
            $this->setLastChild();
        }
        return $this->last_child;
    }
}
