<?php

namespace frontend\models;
use frontend\models\CrossCell;
use frontend\models\CrossWord;
use frontend\models\CrossGlobalsVariables;

class CrossGrid
{
    public $rows;
    public $cols;
    public $cells 		= array();
    public $words 		= array();
    public $words_id 		= array();

    public $inum_v,$inum_h = 0;
    public $maxinum 	= 0;
    public $totwords 	= 0;


    /**
     * Construct
     */
    function __construct($rows, $cols)
    {
        $this->rows = (int)$rows;
        $this->cols = (int)$cols;

        $this->initCells();
    }

    /**
     * Initialize cells (create cell objects)
     */
    private function initCells()
    {
        for ($y = 0; $y < $this->rows; $y++)
            for ($x = 0; $x < $this->cols; $x++)
                $this->cells[$x][$y] =& new CrossCell($x, $y);
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
        $n = $axis == CrossGlobalsVariables::CROSS_HORIZONTAL ? $this->cols : $this->rows;
        $n = mb_strlen($word);
        $n = floor($n / 2);
        return $n;
    }

    /**
     * Place word
     */
    function placeWord($word, $word_id, $x, $y, $axis)
    {
        $w =& new CrossWord($word, $axis, $word_id, $this->cells[$x][$y]);

        ++$this->maxinum;
        if($w->axis==1){
            $this->inum_h = $this->inum_h+1;
        }
        if($w->axis==2){
            $this->inum_v = $this->inum_v+1;

        }

        $this->words[] =& $w;
        $cx = $x;
        $cy = $y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $s = $x;

            for ($i = 0; $i < mb_strlen($word); $i++)
            {
                $cx = $s + $i;
                $cell =& $this->cells[$cx][$cy];

                $cell->setLetter(mb_substr($w->word, $i, 1), $axis, $this);
                $w->cells[$i] =& $cell;
            }

            $cx = $s - 1;
            if ($cx >= 0 )
                $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);

            if($w->axis==1)
                @$this->cells[$cx][$cy]->number = $this->inum_h;
            if($w->axis==2)
                @$this->cells[$cx][$cy]->number = $this->inum_v;

            $cx = $s + mb_strlen($word);
            if(isset($this->cells[$cx][$cy])){
                if (@is_object($this->cells[$cx][$cy]))
                    @$this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
            }else{

            }

        }
        else
        {
            $s = $y;

            for ($i = 0; $i < mb_strlen($word); $i++)
            {
                $cy = $s + $i;
                $cell =& $this->cells[$cx][$cy];

                $cell->setLetter(mb_substr($w->word, $i, 1), $axis, $this);
                $w->cells[$i] =& $cell;
            }

            $cy = $s - 1;
            if ($cy >= 0 )
                $this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
            if($w->axis==1)
                @$this->cells[$cx][$cy]->number = $this->inum_h;
            if($w->axis==2)
                @$this->cells[$cx][$cy]->number = $this->inum_v;

            $cy = $s + mb_strlen($word);
            if(isset($this->cells[$cx][$cy]))
            if (is_object(@$this->cells[$cx][$cy]))
                @$this->cells[$cx][$cy]->setCanCross(CrossGlobalsVariables::CROSS_BOTH, false);
        }
    }

    /**
     * Get random word from the grid (not fully crossed)
     */
    function getRandomWord()
    {
        $words = array();

        for ($i = 0; $i < count($this->words); $i++){
            if (!$this->words[$i]->isFullyCrossed())
                $words[] = $i;
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
    function &getStartCell(&$cell, $axis )
    {
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
            $n =& $x;
        else
            $n =& $y;

        while ($n >= 0)
        {
            if (!$this->cells[$x][$y]->canCross($axis))
                break;

            $n--;

            if (isset($this->cells[$x][$y]->letter))
            {
                $n++;
                break;
            }
        }

        $n++;

        return $this->cells[$x][$y];
    }

    /**
     * Get maximum ending cell on the axis
     */
    function &getEndCell(&$cell, $axis)
    {
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $n =& $x;
            $max = $this->getCols() - 1;
        }
        else
        {
            $n =& $y;
            $max = $this->getRows() - 1;
        }

        while ($n <= $max)
        {
            if (!$this->cells[$x][$y]->canCross($axis))
                break;

            $n++;

            if (isset($this->cells[$x][$y]->letter))
            {
                $n--;
                break;
            }
        }

        $n--;

        return $this->cells[$x][$y];
    }

    /**
     * Count words in the grid
     */
    function countWords()
    {
        $this->totwords = count($this->words);
        return $this->totwords;
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
//        echo "<pre>";
//        var_dump($word);
        for ($i = 0; $i < mb_strlen($word); $i++)
        {
            if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL )
                $cell =& $this->cells[$x+$i][$y];
            else
                $cell =& $this->cells[$x][$y+$i];
//            var_dump($cell);
            if (!is_object($cell))
            {
                echo "ERROR!!! Word: $word, x=$x, y=$y, axis=$axis";
                echo $this->getHTML(1);
            }
//            var_dump(mb_substr($word, $i, 1), $axis);
            if (!$cell->canSetLetter(mb_substr($word, $i, 1), $axis, $this))
                return false;
        }
        return true;
    }

    /**
     * Get HTML (for debugging)
     */
    function getHTML($params = array())
    {
        extract((array)$params);
        $fillflag = 1;
        $cellflag = 'A';
        $color = "pink";

        $html = "<table border=0 class='crossTable' align='center'>";

        for ($y = -1; $y < $this->rows; $y++)
        {
            $html.= "<tr align='center'>";

            for ($x = -1; $x < $this->cols; $x++)
            {

                $class = isset($this->cells[$x][$y]->letter) ? 'cellLetter' : 'cellEmpty';

                    $color = "white";
//                    $class = 'cellDebug';

                $html .= "\n";

                if (isset($this->cells[$x][$y]->number)) {
                    $tempinum = $this->cells[$x][$y]->number;
                    $html.= "<td class='cellNumber".$cellflag."' align='center' valign='middle'><b>".$tempinum."</b></td>"; # sandy addition
                }
                elseif ($y == -1)
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif ($x == -1)
                    $html.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif (isset($this->cells[$x][$y]->letter))
                {
                    if ($fillflag) {
                        $letter=$this->cells[$x][$y]->letter;
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