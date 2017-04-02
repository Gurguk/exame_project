<?php

namespace frontend\components;

use common\models\CrossCell;
use common\models\CrossGlobalsVariables;
use common\models\CrossWord;

class CrossCellActions
{

    public $cell_id;

    /**
     * Construct
     */
    function __construct($cell_id)
    {
        $this->cell_id = $cell_id;
    }

    /**
     * Set letter to the cell
     */
    function setLetter($word_id, $letter, $axis)
    {
//        $cell = CrossCell::findOne($this->cell_id);
//        if (!$this->canSetLetter($letter, $axis))
//        {
//            echo "ERROR IN GRID:";
//            var_dump("Can't place letter '".$letter."' to cell [".$cell->x."x".$cell->y." cell_id= ".$this->cell_id."]");
//        }

        $cell = CrossCell::findOne($this->cell_id);
        $cell->letter = $letter;
        $cell->crossed = $cell->crossed+1;
        if($axis==CrossGlobalsVariables::CROSS_HORIZONTAL)
            $cell->can_cross_h = 0;
        else
            $cell->can_cross_v = 0;
        $cell->word_id = $word_id;
        $cell->save();
        $starts1 = microtime(true);
        $this->updateNeighbours($axis);
        return $this->cell_id;
    }

    /**
     * Set number to the cell
     */
    function SetNumber($word_id, $number)
    {
        $cell = CrossCell::findOne($this->cell_id);
        $cell->number = $number;
        $cell->word_id = $word_id;
        $cell->can_cross_h = 0;
        $cell->can_cross_v = 0;
        $cell->crossed = 2;
        $cell->save();
        $word = CrossWord::findOne($word_id);
        $word->number = $number;
        $word->save();
    }

    /**
     * Update neigbhour cells
     */
    private function updateNeighbours($axis)
    {
        $cell = CrossCell::findOne($this->cell_id);

        $x = $cell->x;
        $y = $cell->y;
        $grid_id = $cell->grid_id;
        $all = CrossCell::find()->where(['grid_id'=>$grid_id])->andWhere(['between', 'x', $x-1, $x+1 ])->andWhere(['between', 'y', $y-1, $y+1 ])->all();
        $grid = [];


        foreach($all as $one){
            $grid[$one->x][$one->y] = $one->id;
        };
        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $y-=1;
            if ($y >= 0)
                $this->setCanCross( $axis, false, $grid[$x][$y]);
            $y+= 2;
            $this->setCanCross($axis, false, $grid[$x][$y]);
        }
        else{
            $x-=1;
            if ($x >= 0)
                $this->setCanCross( $axis, false, $grid[$x][$y]);

            $x+= 2;
            $this->setCanCross($axis, false, $grid[$x][$y]);
        }
    }

    /**
     * Check if the cell can cross
     */
    function canCross($axis)
    {
        $cell = CrossCell::findOne($this->cell_id);
        if($axis==CrossGlobalsVariables::CROSS_HORIZONTAL)
            return 	$cell->can_cross_h;
        else
            return 	$cell->can_cross_v;
    }

    /**
     * Set crossing possiblities
     */
    function setCanCross($axis, $can, $cell_id=0)
    {
        if($cell_id==0)
            $cell = CrossCell::findOne($this->cell_id);
        else
            $cell = CrossCell::findOne($cell_id);

        switch ($axis)
        {
            case CrossGlobalsVariables::CROSS_HORIZONTAL:
                $cell->can_cross_h = $can;
                break;

            case CrossGlobalsVariables::CROSS_VERTIKAL:
                $cell->can_cross_v = $can;
                break;

            case CrossGlobalsVariables::CROSS_BOTH:
                $cell->can_cross_h = $can;
                $cell->can_cross_v = $can;
                break;

            default:
                die("INVALID AXIS FOR setCanCross");
        }
        $cell->save(false);
    }

    /**
     * Check if it's possible to set letter
     */
    function canSetLetter($letter, $axis)
    {
        $closest = CrossCell::findOne($this->cell_id);
        $x = $closest->x;
        $y = $closest->y;
        if($axis==CrossGlobalsVariables::CROSS_HORIZONTAL)
            $can_cross = $closest->can_cross_h;
        else
            $can_cross = $closest->can_cross_v;
        $crossed = $closest->crossed;
        $let = $closest->letter;
        $grid_id = $closest->grid_id;

        $all = CrossCell::find()->where(['grid_id'=>$grid_id])->andWhere(['between', 'x', $x-1, $x+1 ])->andWhere(['between', 'y', $y-1, $y+1 ])->all();

        $grid = [];
        foreach($all as $one){
            $grid[$one->x][$one->y] = $one->letter;
        }
        if($closest->number!=0)
            return false;
        if($can_cross && !$crossed)
        {
            if($axis==CrossGlobalsVariables::CROSS_VERTIKAL){
                if(isset($grid[$x][$y+1]) && isset($grid[$x][$y-1])) {
                    if (($grid[$x][$y+1]!='' || $grid[$x][$y-1]!=''))
                        return false;
                }
                else
                    if(isset($grid[$x][$y+1])){
                        if (($grid[$x][$y + 1]!=''))
                            return false;
                    }
                    else
                        if(isset($grid[$x][$y-1])){
                            if (($grid[$x][$y-1]!=''))
                                return false;
                        }
            }
            else {
                if(isset($grid[$x+1][$y]) && isset($grid[$x-1][$y])) {
                    if (($grid[$x + 1][$y]!='' || $grid[$x - 1][$y]!=''))
                        return false;
                }
                if(isset($grid[$x+1][$y])){
                    if (($grid[$x+1][$y]!=''))
                        return false;
                }
                else
                    if(isset($grid[$x-1][$y])){
                        if (($grid[$x-1][$y]!=''))
                            return false;
                    }
            }
        }

        return !(!$can_cross || ($crossed && $let != $letter));
    }

    /**
     * Get available axis for crossing
     */
    function getCanCrossAxis()
    {
        if ($this->canCross(CrossGlobalsVariables::CROSS_HORIZONTAL) && $this->canCross(CrossGlobalsVariables::CROSS_VERTIKAL)) return CrossGlobalsVariables::CROSS_BOTH;

        elseif ($this->canCross(CrossGlobalsVariables::CROSS_HORIZONTAL)) return CrossGlobalsVariables::CROSS_HORIZONTAL;

        elseif ($this->canCross(CrossGlobalsVariables::CROSS_VERTIKAL)) return CrossGlobalsVariables::CROSS_VERTIKAL;

        else return CrossGlobalsVariables::CROSS_NONE;
    }
}


