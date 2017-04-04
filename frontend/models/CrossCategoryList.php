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

    public function getCrossGrid()
    {
        return $this->hasMany(CrossGrid::className(), ['category' => 'id']);
    }

    public function getCrossSectionList()
    {
        return $this->hasMany(CrossSectionList::className(), ['id_category' => 'id']);
    }

    public function getCrossGridCount()
    {
        $cross_category_list = CrossGrid::find()->joinWith('crossCategoryList',true,'RIGHT JOIN')->where(['cross_grid.category'=>'cross_category_list.id','cross_grid.category'=>$this->id])->count();

        return $cross_category_list ? $cross_category_list : '';
    }
}