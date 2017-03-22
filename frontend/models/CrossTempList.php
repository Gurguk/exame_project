<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class CrossTempList extends ActiveRecord
{
    public $cnt;
    /**
     * CrossCategoryList
     */
    public static function tableName()
    {
        return '{{%cross_temp_list}}';
    }

    public function rules()
    {
        return [
            [['groupid'], 'integer'],
            [['wordid'], 'integer']
        ];
    }

    public function getWords() {
        return $this->hasMany(CrossStringList::className(), ['id' => 'wordid']);
    }
}