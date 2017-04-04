<?php

namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

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

    public function getCrossCategoryList()
    {
        return $this->hasOne(CrossCategoryList::className(), ['id' => 'category']);
    }

    public function getCrossCategoryListName()
    {
        $cross_category_list = $this->crossCategoryList;

        return $cross_category_list ? $cross_category_list->name : '';
    }
}