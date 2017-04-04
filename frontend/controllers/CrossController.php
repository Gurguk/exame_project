<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\components\CrosswordComponent;
use frontend\models\CrossCategoryList;
use frontend\models\CrossSectionList;
use frontend\models\CrossGrid;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CrossController extends Controller
{

    public function actionIndex()
    {
        $category = Yii::$app->request->post('CrossCategoryList');
        $section = Yii::$app->request->post('CrossSectionList');
        $crossword = new CrosswordComponent($category['id'], $section['id'],15);
        $grid = $crossword->GetGrid();
        $cross = $crossword->GetHtml();

        return $this->render('index',['grid'=>$grid, 'cross'=>$cross]);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionForm()
    {
        $model = new CrossCategoryList();
        $categories = ArrayHelper::map(CrossCategoryList::find()->all(), 'id', 'name');

        return $this->render('form', ['model'=>$model,'categories' => $categories]);
    }

    public function actionSections()
    {
        $model = new CrossSectionList();
        $sections = ArrayHelper::map(CrossSectionList::find()->where(['id_category'=>Yii::$app->request->post('id')])->all(), 'id', 'name');
        $select = Html::activeDropDownList($model, 'id',$sections);
        echo json_encode(array('select'=>$select));
        exit;
    }

    public function actionReady()
    {
        $data = CrossCategoryList::find()->all();
//        var_dump($data->crossGridCount);

        return $this->render('ready',['data'=>$data]);
    }

    public function actionHistory()
    {
        $data = CrossGrid::find()->all();
//        var_dump($data->crossGridCount);

        return $this->render('history',['data'=>$data]);
    }

    public function actionShow()
    {
        $crossword = new CrosswordComponent();
        $id = Yii::$app->request->get('cross_id');
        $grid = $crossword->GetGrid($id);
        $cross = $crossword->GetHtml($id);

        return $this->render('index',['grid'=>$grid, 'cross'=>$cross]);
    }


}