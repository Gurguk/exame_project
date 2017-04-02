<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class CrossCategoryList extends ActiveRecord
{
    /**
     * CrossCategoryList
     */
    public static function tableName()
    {
        return '{{%cross_category_list}}';
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string']
        ];
    }
}