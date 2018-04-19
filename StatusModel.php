<?php
namespace andmemasin\myabstract;

/**
 * Class StatusModel
 * @property integer $id
 * @property string $label
 *
 * @package andmemasin\myabstract
 */
class StatusModel extends StaticModel implements StatusInterface
{
    /** @var  integer $id*/
    public $id;

    /** @var  string $label */
    public $label;

    public static $keyColumn = 'id';

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


    public static function isStatus($id){
        return (!self::getById($id)==false);
    }

    public static function getStatusLabel($id){
        $status = self::getById($id);
        if(!empty($status)) {
            return $status->label;
        }
        return null;
    }
}