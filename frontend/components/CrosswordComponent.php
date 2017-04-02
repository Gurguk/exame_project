<?php

namespace frontend\components;

use Yii;
use yii\base\Component;
use frontend\components\CrossBuilder;

class CrosswordComponent extends Component
{
    public $cross;
    public $grid_id;

    function __construct($category,$section,$max_words)
    {
        $this->cross = new CrossBuilder($category, $section, $max_words);
    }

    public function GetGrid()
    {
        $grid_id = $this->cross->buildCross();
        $this->grid_id = $grid_id;
        $return = $this->cross->getHtml($grid_id);

        return $return;
    }

    public function GetHtml()
    {
        return $this->cross->getWords();
    }

}