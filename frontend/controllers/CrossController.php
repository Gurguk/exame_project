<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\components\CrosswordComponent;

class CrossController extends Controller
{

    public function actionIndex()
    {
//        $model = new CrosswordModel();
        $_REQUEST['category'] = 7;
        $_REQUEST['section'] = 3;
        $_REQUEST['max_words'] = 6;
        $crossword = new CrosswordComponent;
        $grid = $crossword->GetGrid($_REQUEST['category'], $_REQUEST['section'],$_REQUEST['max_words']);
//        var_dump(Yii::$app->crossword);
//        $cross = $model->getWords();
//        $grid = $model->getHtml();
//        return $this->render('index',['cross'=>$cross,'grid'=>$grid]);
//        return $this->render('index');
        return $this->render('index',['grid'=>$grid]);
    }
}