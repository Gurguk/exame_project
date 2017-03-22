<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\CrosswordModel;

class CrossController extends Controller
{

    public function actionIndex()
    {
        $model = new CrosswordModel();
        $_REQUEST['category'] = 7;
        $_REQUEST['section'] = 3;
        $_REQUEST['max_words'] = 6;
        $model->buildCross($_REQUEST['category'], $_REQUEST['section'],$_REQUEST['max_words']);
//        $cross = $model->getWords();
        $grid = $model->getHtml();
        return $this->render('index',['cross'=>$cross,'grid'=>$grid]);
    }
}