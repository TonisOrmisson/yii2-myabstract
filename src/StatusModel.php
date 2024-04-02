<?php
namespace andmemasin\myabstract;

use Yii;
use andmemasin\myabstract\interfaces\StatusInterface;

/**
 * Class StatusModel
 * @property integer $id
 * @property string $label
 *
 * @package andmemasin\myabstract
 */
class StatusModel extends StaticModel implements StatusInterface
{
    const STATUS_CREATED = "created";
    const STATUS_ACTIVE = "active";

    public string|int $id;

    /** @var  string $label */
    public string $label = '';

    /** @var string */
    public string $description = '';

    public static string $keyColumn = 'id';

    public function getModelAttributes() : array
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
    public static function getAllStatusNames() : array
    {
        $out = [];
        $baseModel = Yii::createObject(static::class);
        $modelsAttributes = $baseModel->getModelAttributes();
        foreach ($modelsAttributes as $attributes) {
            $out[] = strval($attributes['label']);
        }
        return $out;
    }

    public static function isStatus(string|int $id) : bool
    {
        return (!self::getById($id) === false);
    }

    public static function getStatusLabel(string|int $id) : string
    {
        $status = self::getById($id);
        if (!empty($status)) {
            return $status->label;
        }
        return '';
    }

    public function isActive(string|int $id) : bool
    {
        throw new \Exception('not implemented');
    }
}