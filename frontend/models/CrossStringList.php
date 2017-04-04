<?php
namespace frontend\models;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use yii\helpers\Console;
use Yii;

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

    public function addDemo($data)
    {
        $postModel = new CrossStringList();
        Console::startProgress(0,count($data));
        $i = 1;
        while($i<count($data)){
            $rows = [];
            foreach($data[$i] as $val){
                $rows[] = [
                    'id',
                    'id_category' => 1,
                    'id_section' => 1,
                    'length' => mb_strlen($val['word']),
                    'value' => $val['word'],
                    'url' => $val['question']
                ];
            }
            Yii::$app->db->createCommand()->batchInsert(CrossStringList::tableName(), $postModel->attributes(), $rows)->execute();
            Console::updateProgress($i,count($data));
            $i++;
        }
        Console::endProgress("end".PHP_EOL);

    }
}