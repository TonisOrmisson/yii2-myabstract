<?php
namespace andmemasin\myabstract;

use Yii;

/**
 * Class StatusModel
 * @property integer $id
 * @property string $label
 * @property boolean $isActive
 *
 * @package andmemasin\myabstract
 */
class StatusModel extends StaticModel implements StatusInterface
{
    const STATUS_CREATED = "created";
    const STATUS_ACTIVE = "active";

    /** @var  integer $id*/
    public $id;

    /** @var  string $label */
    public $label;

    /** @var string */
    public $description;

    public static $keyColumn = 'id';

    public static function getModels()
    {
        return [
            self::STATUS_CREATED => [
                'id' => self::STATUS_CREATED,
                'label' => Yii::t('app', 'Created'),
                'description' => Yii::t('app', 'Was created'),
            ],

        ];
    }

    /**
     * Returns all status names in plain array without labels
     * @return string[]
     */
    public static function getAllStatusNames()
    {
        $out = [];
        foreach (self::getModels() as $status) {
            $out[] = $status->label;
        }
        return $out;
    }

    /**
     * @param string $id
     * @return bool
     */
    public static function isStatus($id) {
        return (!self::getById($id) === false);
    }

    /**
     * @param string $id
     * @return null|string
     */
    public static function getStatusLabel($id) {
        $status = self::getById($id);
        if (!empty($status)) {
            return $status->label;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return true;
    }
}