<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class CrossStringList extends ActiveRecord
{
    /**
     * CrossStringList
     */
    public static function tableName()
    {
        return '{{%cross_string_list}}';
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id_category'], 'integer'],
            [['id_section'], 'integer'],
            [['length'], 'integer'],
            [['value'], 'string'],
            [['question'], 'string']
        ];
    }

    public function getTempCross()
    {
        return $this->hasOne(CrossTempList::className(), ['wordid' => 'id']);
    }
}