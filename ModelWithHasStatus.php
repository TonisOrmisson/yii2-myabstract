<?php

namespace andmemasin\myabstract;

use andmemasin\myabstract\traits\ModelWithHasStatusTrait;
use andmemasin\survey\Status;
use yii\base\InvalidConfigException;

/**
 * Class ModelWithHasStatus
 *
 * @property string $status
 *
 * @property HasStatusModel[] $hasStatuses
 * @property Status $statusModel
 * @property Status $currentStatus
 * @property HasStatusModel $hasStatus
 *
 * @package andmemasin\myabstract
 * @author Tonis Ormisson <tonis@andmemasin.eu>
 */
class ModelWithHasStatus extends MyActiveRecord
{
    use ModelWithHasStatusTrait;


    public function init()
    {
        if(!$this->hasStatusClassName){
            throw new InvalidConfigException('hasStatusClassName must be set for '.static::className());
        }
        parent::init();
    }


}