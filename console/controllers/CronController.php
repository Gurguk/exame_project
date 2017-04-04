<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 27.12.16
 * Time: 16:24
 */

namespace console\controllers;


use yii\console\Controller;
use common\models\ScrapCrosswords;
use Yii;

class CronController extends Controller {

    public function actionScrap() {
        $model = new ScrapCrosswords();
        $model->scrapWords(51,100,'http://www.kotvet.ru/odn/');
    }

} 