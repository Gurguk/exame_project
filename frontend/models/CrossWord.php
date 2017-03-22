<?php

namespace frontend\models;

use frontend\models\CrossGlobalsVariables;

class CrossWord
{
    public $word;
    public $word_id;
    public $axis;
    public $cells = array();
    public $fully_crossed = FALSE;
    public $inum_h = 0;
    public $inum_v = 0;

    /**
     * Construct
     */
    function __construct($word, $axis,$word_id)
    {
        $this->word = $word;
        $this->axis = $axis;
        $this->word_id = $word_id;
    }

    /**
     * Get word start X
     */
    function getStartX()
    {
        return $this->cells[0]->x;
    }

    /**
     * Get word start Y
     */
    function getStartY()
    {
        return $this->cells[0]->y;
    }

    /**
     * Get crossable cells in the word
     */
    function getCrossableCells()
    {
        $axis = $this->getCrossAxis();

        $cells = array();
//        echo '<pre>';
//        var_dump(mb_strlen($this->word),$this->cells);
//        die;
        for ($i = 0; $i < mb_strlen($this->word); $i++){
            if(isset($this->cells[$i]))
            if ($this->cells[$i]->canCross($axis))
                $cells[] =&  $this->cells[$i];
        }


        if (!count($cells) )
            $this->fully_crossed = true;

        return $cells;
    }

    /**
     * Check if word is fully crossed
     */
    function isFullyCrossed()
    {
        if ($this->fully_crossed )
            return true;

        $this->getCrossableCells();

        return $this->fully_crossed;
    }

    /**
     * Get crossing axis
     */
    function getCrossAxis()
    {
        return $this->axis == CrossGlobalsVariables::CROSS_HORIZONTAL ? CrossGlobalsVariables::CROSS_VERTIKAL : CrossGlobalsVariables::CROSS_HORIZONTAL;
    }
}