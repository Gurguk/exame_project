<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class CrossWord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%cross_word}}';
    }

    public function rules()
    {
        return [
            [['word_id'], 'integer'],
            [['axis'], 'integer'],
            [['fully_crossed'], 'integer'],
            [['inum_h'], 'integer'],
            [['inum_v'], 'integer'],
            [['x'], 'integer'],
            [['y'], 'integer'],
            [['length'], 'integer'],
            [['grid_id'], 'integer'],
        ];
    }
}