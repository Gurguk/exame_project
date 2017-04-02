<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
//use common\models\CrossCell;
//use common\models\CrossWord;
//use common\models\CrossGlobalsVariables;

class CrossGrid extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%cross_grid}}';
    }

    public function rules()
    {
        return [
            [['rows'], 'integer'],
            [['cols'], 'integer'],
            [['totwords'], 'integer'],
            [['crossword'], 'integer'],
        ];
    }
}