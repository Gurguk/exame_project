<?php


namespace common\models;

use Yii;
use common\models\CrossGlobalsVariables;
use yii\db\ActiveRecord;

/**
 * CrossCell model
 *
 * @property integer $id
 * @property integer $grid_id
 * @property integer $x
 * @property integer $y
 * @property integer $crossed
 * @property string $letter
 * @property integer $number
 * @property integer $word_id
 */

class CrossCell extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%cross_cell}}';
    }

}