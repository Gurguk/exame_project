<?php

namespace frontend\components;

use common\models\CrossCell;
use common\models\CrossWord;
use common\models\CrossGlobalsVariables;

class CrossWordAction
{
    public $id;
    public $word_id;
    public $word;
    public $axis;
    public $cells = array();
    public $fully_crossed = false;
    public $inum_h = 0;
    public $inum_v = 0;
    public $x;
    public $y;
    public $grid_id;
    public $length = 0;

    /**
     * Construct
     */
    function __construct($word, $axis, $word_id, $x, $y, $grid_id)
    {

        $this->word = $word;
        $this->length = mb_strlen($word);
        $this->axis = $axis;
        $this->word_id = $word_id;
        $this->x = $x;
        $this->y = $y;
        $this->grid_id = $grid_id;
        $this->initWord();

    }

    function initWord()
    {
        $word = new CrossWord();
        $word->word_id = $this->word_id;
        $word->axis = $this->axis;
        $word->fully_crossed = 0;
        $word->x = (int)$this->x;
        $word->y = (int)$this->y;
        $word->length = $this->length;
        $word->grid_id = $this->grid_id;
        if($word->save()) {
            $this->id = $word->getPrimaryKey();
            return;
        }
        else
            return null;
    }

    /**
     * Get word start X
     */
    function getStartX()
    {
        return $this->x;
    }

    /**
     * Get word start Y
     */
    function getStartY()
    {
        return $this->y;
    }

    /**
     * Get crossable cells in the word
     */
    function getCrossableCells($grid)
    {
        $axis = $this->getCrossAxis();
        $word = CrossWord::findOne($this->id);

        if($axis==CrossGlobalsVariables::CROSS_HORIZONTAL)
            $cell_arr = CrossCell::find()->where(['word_id'=>$this->id, 'x'=>$word->x])
                ->andWhere(['between', 'y', $word->y, $word->y+$word->length ])->orderBy(['y'=>SORT_ASC])->all();
        else
            if($axis==CrossGlobalsVariables::CROSS_VERTIKAL) {
                $cell_arr = CrossCell::find()->where(['word_id' => $this->id, 'y' => $word->y])
                    ->andWhere(['between', 'x', $word->x, $word->x + $word->length])->orderBy(['x' => SORT_ASC])->all();
            }

        $cells = array();
        for ($i = 0; $i < count($cell_arr); $i++){
            $c = $grid->cells[$cell_arr[$i]->x][$cell_arr[$i]->y];
            if(isset($c))
                if ($c->canCross($axis))
                    $cells[] =  $c;
        }

//        var_dump($axis,count($cells),$word->id, $word->fully_crossed);
//        var_dump('-------');
        if (count($cells)==0 ) {
            $word->fully_crossed = true;
            $word->save();
        }

        return $cells;
    }

    /**
     * Check if word is fully crossed
     */
    function isFullyCrossed($grid)
    {
        $word = CrossWord::findOne($this->id);
        if ($word->fully_crossed )
            return true;

        $this->getCrossableCells($grid);
        $word = CrossWord::findOne($this->id);
        return $word->fully_crossed;
    }

    /**
     * Get crossing axis
     */
    function getCrossAxis()
    {
        $word = CrossWord::findOne($this->id);
        return $word->axis == CrossGlobalsVariables::CROSS_HORIZONTAL ? CrossGlobalsVariables::CROSS_VERTIKAL : CrossGlobalsVariables::CROSS_HORIZONTAL;
    }
}