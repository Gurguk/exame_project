<?php

namespace frontend\components;

use common\models\CrossCell;
use common\models\CrossGrid;
use common\models\CrossWord;
use common\models\CrossGlobalsVariables;
use frontend\components\CrossCellActions;
use frontend\components\CrossWordAction;
use Yii;
use yii\db\Expression;

class CrossGridActions
{
    public $rows;
    public $cols;
    public $cells 		= array();
    public $words 		= array();
    public $words_id 		= array();
    public $inum_v,$inum_h = 0;
    public $totwords 	= 0;
    public $cross_id;
    public $grid_id;

    /**
     * Construct
     */
    function __construct($cross_id, $rows = 20, $cols = 20)
    {
        $this->rows = (int)$rows;
        $this->cols = (int)$cols;
        $this->cross_id = $cross_id;
        $this->initGrid();
        $this->initCells();
    }

    /**
     * Initialize grid
     */
    private function initGrid()
    {
        $temp_grid = new CrossGrid();

        $temp_grid->rows = $this->rows;
        $temp_grid->cols = $this->cols;
        $temp_grid->totwords = $this->totwords;
        $temp_grid->crossword = $this->cross_id;

        if ($temp_grid->save()) {
            $this->grid_id = $temp_grid->getPrimaryKey();
            return $this->grid_id;
        }
        //$this->addError('Initialize grid');
        return null;
    }

    /**
     * Initialize cells (create cell objects)
     */
    private function initCells()
    {
        $data = [];
        $sql = "INSERT into cross_cell (`grid_id`, `x`, `y`, `crossed`, `letter`, `number`, `word_id`, `can_cross_h`, `can_cross_v`) VALUES ";
        for ($y = 0; $y < $this->rows; $y++) {
            for ($x = 0; $x < $this->cols; $x++) {
                if($x==0)
                    $data[] = "(".$this->grid_id.", ".$x.", ".$y.", 0, '', 0, 0, 0, 1)";
                else if($y==0)
                    $data[] = "(".$this->grid_id.", ".$x.", ".$y.", 0, '', 0, 0, 1, 0)";
                    else
                        $data[] = "(".$this->grid_id.", ".$x.", ".$y.", 0, '', 0, 0, 1, 1)";
            }
        }
        $sql .= implode(',', $data);
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sql);
        $command -> execute();
        $cells = CrossCell::find()->where(['grid_id'=>$this->grid_id])->all();
        foreach($cells as $cell){
            $cell_o[$cell->x][$cell->y] = new CrossCellActions($cell->id);
        }
        $this->cells = $cell_o;
    }

    /**
     * Get number of columns in the grid
     */
    function getCols()
    {
        return $this->cols;
    }

    /**
     * Get center position
     */
    function getCenterPos($axis, $word = '')
    {
        $n = $axis == CrossGlobalsVariables::CROSS_VERTIKAL ? $this->cols : $this->rows;
        $n = $n - mb_strlen($word);
        $n = floor($n / 2);
        return $n;
    }

    /**
     * Place word
     */
    function placeWord($word, $word_id, $x, $y, $axis)
    {
        $w = new CrossWordAction($word, $axis, $word_id, $x, $y, $this->grid_id);
        $this->words[$w->id] = $w;
        if($w->axis==1){
            $this->inum_h = $this->inum_h+1;
        }
        if($w->axis==2){
            $this->inum_v = $this->inum_v+1;

        }

        $cx = $x;
        $cy = $y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $s = $x;

            for ($i = 0; $i < mb_strlen($word); $i++)
            {
                $cx = $s + $i;
                $cell = $this->cells[$cx][$cy];
                if (is_object($cell)){
                    $cell->setLetter($w->id, mb_substr($w->word, $i, 1), $axis);
                }
                else
                    break;
                if($i==0)
                {
                    $w->x = $cx;
                    $w->y = $cy;
                }

            }
            $cx = $s - 1;
            if ($cx >= 0 )
                $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);

            if($w->axis==CrossGlobalsVariables::CROSS_HORIZONTAL)
                $this->cells[$cx+1][$cy]->SetNumber($w->id, $this->inum_h);
            if($w->axis==CrossGlobalsVariables::CROSS_VERTIKAL)
                $this->cells[$cx+1][$cy]->SetNumber($w->id, $this->inum_v);

            $cx = $s + mb_strlen($word);
            if(isset($this->cells[$cx][$cy])){
                if (is_object($this->cells[$cx][$cy]))
                    $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
            }

        }
        else
        {
            $s = $y;

            for ($i = 0; $i < mb_strlen($word); $i++)
            {
                $cy = $s + $i;
                $cell = $this->cells[$cx][$cy];
                if (is_object($cell))
                    $cell->setLetter($w->id, mb_substr($w->word, $i, 1), $axis);
                else
                    break;
                if($i==0)
                {
                    $w->x = $cx;
                    $w->y = $cy;
                }
            }

            $cy = $s - 1;
            if ($cy >= 0 )
                $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
            if($w->axis==1)
                $this->cells[$cx][$cy+1]->SetNumber($w->id, $this->inum_h);
            if($w->axis==2)
                $this->cells[$cx][$cy+1]->SetNumber($w->id, $this->inum_v);

            $cy = $s + mb_strlen($word);
            if(isset($this->cells[$cx][$cy]))
                if (is_object($this->cells[$cx][$cy]))
                    $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
        }
        $grid = CrossGrid::findOne($this->grid_id);
        $grid->totwords = $grid->totwords+1;
        $grid->save();
    }

    /**
     * Get random word from the grid (not fully crossed)
     */
    function getRandomWord()
    {
        $words = array();
        $words_arr = $this->words;
        foreach ($words_arr as $word){
            $id = $word->id;
            if (!$this->words[$id]->isFullyCrossed($this))
                $words[] = $id;
        }
        if (!count($words))
            return 10;
        $n = array_rand($words);
        $n = $words[$n];

        return $this->words[$n];
    }
    /**
     * Get minimum starting cell on the axis
     */
    function getStartCell($cell, $axis )
    {
        $cell = CrossCell::findOne($cell->cell_id);
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL){
            while ($x >= 0)
            {
                if (!$this->cells[$x][$y]->canCross($axis))
                    break;

                $x--;

                if (isset($this->cells[$x][$y]->letter))
                {
                    $x++;
                    break;
                }
            }

            $x++;

            return $this->cells[$x][$y];
        }
        else
        {
            while ($y >= 0)
            {
                if (!$this->cells[$x][$y]->canCross($axis))
                    break;

                $y--;

                if (isset($this->cells[$x][$y]->letter))
                {
                    $y++;
                    break;
                }
            }

            $y++;

            return $this->cells[$x][$y];
        }

    }

    /**
     * Get maximum ending cell on the axis
     */
    function getEndCell($cell, $axis)
    {
        $cell = CrossCell::findOne($cell->cell_id);
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $max = $this->getCols() - 1;
            while ($x <= $max)
            {
                if (!$this->cells[$x][$y]->canCross($axis))
                    break;

                $x++;

                if (isset($this->cells[$x][$y]->letter))
                {
                    $x--;
                    break;
                }
            }

            $x--;

            return $this->cells[$x][$y];
        }
        else
        {
            $max = $this->getRows() - 1;
            while ($y <= $max)
            {
                if (!$this->cells[$x][$y]->canCross($axis))
                    break;

                $y++;

                if (isset($this->cells[$x][$y]->letter))
                {
                    $y--;
                    break;
                }
            }
            $y--;

            return $this->cells[$x][$y];
        }

    }

    /**
     * Count words in the grid
     */
    function countWords()
    {
        $words = CrossGrid::findOne($this->grid_id);

        return $words->totwords;
    }

    /**
     * Get number of rows in the grid
     */
    function getRows()
    {
        return $this->rows;
    }

    /**
     * Check if it's possible to place the word
     */
    function canPlaceWord($word, $x, $y, $axis)
    {
        for ($i = 0; $i < mb_strlen($word); $i++)
        {
            if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL )
                $cell = $this->cells[$x+$i][$y];
            else
                $cell = $this->cells[$x][$y+$i];
            if (!is_object($cell))
            {
                echo "ERROR!!! Word: $word, x=$x, y=$y, axis=$axis";
                echo $this->getHTML();
            }
            if (!$cell->canSetLetter(mb_substr($word, $i, 1), $axis))
                return false;
        }
        return true;
    }

    /**
     * Get HTML (for debugging)
     */
    function getHTML($grid_id)
    {
        $grid = CrossGrid::findOne($grid_id);
        $cells = CrossCell::find()->where(['grid_id'=>$grid_id])->all();
        $cell = array();
        foreach($cells as $val)
        {
            $cell[$val->x][$val->y] = $val;
        }
        $fillflag = 1;
        $cellflag = 'A';
        $color = "pink";

        $html = "<table border=0 class='crossTable' align='center'>";

        for ($y = -1; $y < $grid->rows; $y++)
        {
            $html.= "<tr align='center'>";

            for ($x = -1; $x < $grid->cols; $x++)
            {

                $class = isset($cell[$x][$y]->letter) ? 'cellLetter' : 'cellEmpty';

                $color = "white";
//                    $class = 'cellDebug';

                $html .= "\n";

                if (isset($cell[$x][$y]->number)) {
                    $tempinum = $cell[$x][$y]->number;
                    $html.= "<td class='cellNumber".$cellflag."' align='center' valign='middle'><b>".$tempinum."</b></td>";
                }
                elseif ($y == -1)
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif ($x == -1)
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif (isset($cell[$x][$y]->letter))
                {
                    if ($fillflag) {
                        $letter=$cell[$x][$y]->letter;
                    } else {
                        $letter="&nbsp;";
                    }
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'><span class='letter'>$letter</span><span class='field'><input type='text' size='1'></span></td>";
                }
                else
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
            }
            $html.= "</tr>";
        }

        $html.= "</table>";

        return $html;
    }

}