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
        $_REQUEST['max_words'] = 10;
        $crossword = new CrosswordComponent($_REQUEST['category'], $_REQUEST['section'],$_REQUEST['max_words']);
        $grid = $crossword->GetGrid();
        $cross = $crossword->GetHtml();

        return $this->render('index',['grid'=>$grid, 'cross'=>$cross]);
    }
}