<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class CrossSectionList extends ActiveRecord
{
    /**
     * CrossSectionList
     */
    public static function tableName()
    {
        return '{{%cross_section_list}}';
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id_category'], 'integer'],
            [['name'], 'string']
        ];
    }

    public function getCrossSectionList()
    {
        return $this->hasOne(CrossSectionList::className(), ['id' => 'id_category']);
    }
}