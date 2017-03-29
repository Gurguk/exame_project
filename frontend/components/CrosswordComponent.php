<?php

namespace frontend\components;

use Yii;
use yii\base\Component;
use frontend\components\CrossBuilder;

class CrosswordComponent extends Component
{

    public function GetGrid($category,$section,$max_words)
    {
        $cross = new CrossBuilder($category, $section, $max_words);
        $grid_id = $cross->buildCross();
//        die;
//        $grid_id = 2;
        $return = $cross->getHtml($grid_id);

        return $return;
    }

    public function GetHtml()
    {
        return "cross";
    }

}