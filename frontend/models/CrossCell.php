<?php


namespace frontend\models;

use frontend\models\CrossGlobalsVariables;

class CrossCell
{
    public $x;
    public $y;
    public $letter;
    public $crossed = 0;
    public $number;

    public $can_cross = array(
        1 => true,
        2 => true
    );


    /**
     * Construct
     */
    function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Set letter to the cell
     */
    function setLetter($letter, $axis, &$grid)
    {
        if (!$this->canSetLetter($letter, $axis,$grid))
        {
            echo "ERROR IN GRID:";
            echo $grid->getHtml();
            die("Can't place letter '".$letter."' to cell [".$this->x."x".$this->y."]");
        }

        $this->letter = $letter;

        $this->crossed++;

        $this->can_cross[$axis] = false;

        $this->updateNeighbours($axis, $grid);
    }

    /**
     * Update neigbhour cells
     */
    private function updateNeighbours($axis, &$grid)
    {
        $x = $this->x;
        $y = $this->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $y-=1;

            if ($y >= 0)
                $grid->cells[$x][$y]->setCanCross($axis, false);

            $y+= 2;
            if(isset($this->cells[$x][$y]))
            if (is_object($grid->cells[$x][$y]))
                $grid->cells[$x][$y]->setCanCross($axis, false);
        }
        else{
            $x-=1;

            if ($x >= 0)
                $grid->cells[$x][$y]->setCanCross($axis, false);

            $x+= 2;
            if(isset($this->cells[$x][$y]))
            if (is_object($grid->cells[$x][$y]))
                $grid->cells[$x][$y]->setCanCross($axis, false);
        }

    }

    /**
     * Check if the cell can cross
     */
    function canCross($axis)
    {
        return $this->can_cross[$axis];
    }

    /**
     * Set crossing possiblities
     */
    function setCanCross($axis, $can)
    {
        switch ($axis)
        {
            case CrossGlobalsVariables::CROSS_HORIZONTAL:
                $this->can_cross[CrossGlobalsVariables::CROSS_HORIZONTAL] = $can;
                break;

            case CrossGlobalsVariables::CROSS_VERTIKAL:
                $this->can_cross[CrossGlobalsVariables::CROSS_VERTIKAL] = $can;
                break;

            case CrossGlobalsVariables::CROSS_BOTH:
                $this->can_cross[CrossGlobalsVariables::CROSS_HORIZONTAL] = $can;
                $this->can_cross[CrossGlobalsVariables::CROSS_VERTIKAL] = $can;
                break;

            default:
                die("INVALID AXIS FOR setCanCross");
        }
    }

    /**
     * Check if it's possible to set letter
     */
    function canSetLetter($letter, $axis, $grid)
    {
        if($this->can_cross[$axis] && !$this->crossed)
        {
            if($axis==CrossGlobalsVariables::CROSS_HORIZONTAL){
                if(($grid->cells[$this->x][$this->y+1]->letter || $grid->cells[$this->x][$this->y-1]->letter ))
                    return false;
            }
            else {
                if(($grid->cells[$this->x+1][$this->y]->letter || $grid->cells[$this->x-1][$this->y]->letter ))
                    return false;
            }
        }
        return !(!$this->can_cross[$axis] || ($this->crossed && $this->letter != $letter));
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