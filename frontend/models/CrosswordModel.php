<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 18.03.17
 * Time: 16:43
 */

namespace frontend\models;

use Yii;
use frontend\models\CrossStringList;
use frontend\models\CrossTempList;
use frontend\models\CrossGrid;
use yii\db\Expression;
use frontend\models\CrossGlobalsVariables;

class CrosswordModel
{
    public $rows;
    public $cols;
    public $grid;

    public $max_full_tries = 10;
    public $max_words = 15;
    public $items;
    public $max_tries = 50;

    public $match_line;
    public $full_tries = 0;
    public $tries = 0;

    public $crossid = 0;

    /**
     * Construct
     */
    function __construct($rows = 20, $cols = 20)
    {
        $this->rows = $rows;
        $this->cols = $cols;
    }

    /**
     * Build crossword
     */
    public function buildCross($categoryid,$sectionid,$max_words)
    {
        $this->createCrossID();
        $this->setMaxWords($max_words);

        $words = $this->getWordsList($categoryid,$sectionid,$max_words);
        $cross = $this->generateFromWords($words);

        return $cross;
    }

    /**;
     * Create temp crossword ID
     */
    private function createCrossID()
    {
        $this->crossid = time();
    }

    /**;
     * Get temp crossword ID
     */
    private function getCrossID()
    {
        return $this->crossid;
    }

    /**
     * Set max number of words in the crossword
     */
    private function setMaxWords($max_words)
    {
        $this->max_words = $max_words;
    }

    /**
     * Get words list by category, section and count of words
     */
    private function getWordslist($categoryid,$sectionid,$max_words)
    {
        $words = CrossStringList::find()
            ->select('id')
            ->where(['id_category' => $categoryid, 'id_section'=>$sectionid])
            ->orderBy(new Expression('rand()'))
            ->limit($max_words)
            ->asArray()
            ->all();
        return $words;
    }

    /**
     * Save word id in temp cross
     */
    private function insertWordsToTemp($words_list)
    {
        foreach($words_list as $word)
        {
            $temp_cross = new CrossTempList();
            $temp_cross->groupid = $this->getCrossID();
            $temp_cross->wordid = $word['id'];
            $temp_cross->save();
        }

    }

    /**
     * Generate crossword from words list
     */
    private function generateFromWords($words_list)
    {
        $_max_words = $this->max_words;
        $this->insertWordsToTemp($words_list);
        $success = false;
        $required_words = count($words_list);
        while ($required_words > 1)
        {
            $this->setMaxWords($required_words);

            if ($success = $this->generate())
                break;

            $required_words--;
        }
        $this->setMaxWords($_max_words);
        return $success;
    }

    /**
     * Generate crossword
     */
    private function generate()
    {
        $this->full_tries = 0;
        while ($this->full_tries < $this->max_full_tries)
        {
            $this->resetGrid();
            $this->full_tries++;
            $this->placeFirstWord();
            $this->autoGenerate();
            if ($this->grid->countWords() == $this->max_words)
            {
                $this->items = $this->getItems();
                return true;
            }
        }

        if ($this->_debug)
            echo "ERROR: unable to generate {$this->max_words} words crossword (tried {$this->_full_tries} times)";

        return false;
    }

    /**
     * Reset grid
     */
    private function resetGrid()
    {
        $this->grid = new CrossGrid($this->rows, $this->cols);
        $this->tries = 0;
        $this->items = NULL;
    }

    /**
     * Place first word to the cell
     */
    private function placeFirstWord()
    {
        $word = $this->getRandomWord($this->grid->getCols());
        $x = $this->grid->getCenterPos(CrossGlobalsVariables::CROSS_HORIZONTAL, $word->value);
        $y = $this->grid->getCenterPos(CrossGlobalsVariables::CROSS_VERTIKAL);
        $this->grid->placeWord($word->value, $word->id, $x, $y, CrossGlobalsVariables::CROSS_HORIZONTAL);
    }

    /**
     * Try to generate crossword automatically
     */
    private function autoGenerate()
    {
        while ($this->grid->countWords() < $this->max_words && $this->tries < $this->max_tries)
        {
            $this->tries++;

            $w =& $this->grid->getRandomWord();
            if (is_int($w))
                if ($w == CrossGlobalsVariables::WORDS_FULLY_CROSSED)
                {
                    break;
                }
            $axis = $w->getCrossAxis();

            $cells =& $w->getCrossableCells();

            while (count($cells))
            {
                $n = array_rand($cells);

                $cell =& $cells[$n];

                $list =& $this->getWordWithStart($cell, $axis);
                $word = $list[0];
                $start =& $list[1];

                if ($start)
                {
                    $this->grid->placeWord($word['value'], $word['id'], $start->x, $start->y, $axis);
                    break;
                }

                $cells[$n]->setCanCross($axis, false);
                unset($cells[$n]);
            }
        }
    }

    /**
     * Get random word
     */
    private function getRandomWord()
    {
       $count = $this->getWordsCount();

        if (!$count)
            die("ERROR: there is no words to fit in this grid" );

//        $n = rand(0, $count-1);

        $words = CrossTempList::find()->select('wordid')->where(['groupid'=>$this->crossid])->all(); //->offset($n)
        $search = array();
        foreach($words as $word){
            $search[]=$word->wordid;
        }
        $word = CrossStringList::find()->where(['in', 'id', $search])->orderBy(['length'=>SORT_DESC])->one();
//        die;
//        $word = $words->words;

//        var_dump($word);
//        die;

        return $word;
    }

    /**
     * Count words
     */
    private function getWordsCount()
    {

        $count = CrossTempList::find()
            ->select('COUNT(groupid) as cnt')
            ->where(['groupid' => $this->crossid])
            ->one();

        return $count->cnt;
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
     * Try to pick the word crossing the cell
     */
    private function getWordWithStart(&$cell, $axis)
    {
        $start = & $this->grid->getStartCell($cell, $axis);
        $end = & $this->grid->getEndCell($cell, $axis);

        $word = $this->getWord($cell, $start, $end, $axis);

        if (!$word['value']) return false;

        $pos = false;

        do
        {
            @$s_cell = & $this->calcStartCell($cell, $start, $end, $axis, $word['value'], $pos);

            @$can = $this->grid->canPlaceWord($word['value'], $s_cell->x, $s_cell->y, $axis);

        }
        while (!$can);
        return array($word, &$s_cell);
    }

    /**
     * Try to get the word
     */
    private function getWord(&$cell, &$start, &$end, $axis)
    {
        $this->match_line = $this->getMatchLine($cell, $start, $end, $axis);
        $match = $this->getMatchLike($this->match_line);
        $min = $this->getMatchMin($this->match_line);
        $max = mb_strlen($this->match_line);
        $regexp = $this->getMatchRegexp($this->match_line);
        $rs = $this->loadWords($match, $min, $max);

        shuffle($rs);
        $word = '';
        $words=array();
        foreach($this->grid->words as $w){
            $words[]=$w->word;
        }
        foreach($rs as $key=>$val)
        {
            if(!in_array($val['value'],$words))
                if (preg_match("/".$regexp."/u", $val['value']))
                {
                    $word = $val;
                    break;
                }
        }
        if($word=='')
            return false;
        else
            return $word;
    }

    /**
     * Generate word matching line
     */
    private function getMatchLine(&$cell, &$start, &$end, $axis)
    {
        $x = $start->x;
        $y = $start->y;
        $str = '';

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $max = $end->x;
            while ($x <= $max)
            {
                $cell =& $this->grid->cells[$x][$y];
                $str.= isset($cell->letter) ? $cell->letter : '_';
                $x++;
            }
        }
        else
        {
            $max = $end->y;
            while ($y <= $max)
            {
                $cell =& $this->grid->cells[$x][$y];
                $str.= isset($cell->letter) ? $cell->letter : '_';
                $y++;
            }
        }

        return $str;
    }
    /**
     * Get match for the match string
     */
    private function getMatchLike($str)
    {
        $str = preg_replace("/^_+/", "%", $str, 1);
        $str = preg_replace("/_+$/", "%", $str, 1);
        return $str;
    }

    /**
     * Get minimum match string
     */
    private function getMatchMin($str)
    {
        $str = preg_replace("/^_+/", "", $str, 1);
        $str = preg_replace("/_+$/", "", $str, 1);
        return strlen($str);
    }

    /**
     * Get REGEXP for the match string
     */
    private function getMatchRegexp($str)
    {
        $str = preg_replace("/^_*/e", "'^.{0,'.strlen('\\0').'}'", $str, 1);
        $str = preg_replace("/_*$/e", "'.{0,'.strlen('\\0').'}$'", $str, 1);
        $str = preg_replace("/_+/e", "'.{'.strlen('\\0').'}'", $str);
        return $str;
    }

    /**
     * Load words for the match
     */
    private function loadWords($match, $len_min, $len_max)
    {
        $used_words = $this->getUsedWords();

        $sql = "SELECT id, value FROM cross_string_list WHERE 
                  id IN (SELECT wordid FROM cross_temp_list WHERE wordid NOT IN (:words) AND groupid=:groupid) 
                  AND length BETWEEN :len_min AND :len_max AND value LIKE :match";
        $values = array(":words"=>implode(',',$used_words), ":groupid"=>$this->crossid, ":len_min"=>$len_min, ":len_max"=>$len_max, ":match"=>$match);
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sql, $values);
        $result = $command->queryAll();

        return $result;
    }

    /**
     * Get used word
     */
    function getUsedWords()
    {
        $word_list = array();
        foreach ($this->grid->words as $word)
            $word_list[]=$word->word_id;

        return $word_list;
    }

    /**
     * Calculate starting cell for the word
     */
    function &calcStartCell(&$cell, &$start, &$end, $axis, $word, &$pos)
    {
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $t =& $x;
            $s = $cell->x - $start->x;
            $e = $end->x - $cell->x;
        }
        else
        {
            $t =& $y;
            $s = $cell->y - $start->y;
            $e = $end->y - $cell->y;
        }

        $l = mb_strlen($word);

        do
        {
            $offset = isset($pos) ? $pos+1 : 0;
            $pos = mb_strpos($word, $cell->letter, $offset);
            $a = $l-$pos-1;
            if ($pos <= $s && $a <= $e)
            {
                $t-= $pos;
                return $this->grid->cells[$x][$y];
            }
        }
        while ($pos !== false);

        return false;
    }


    /**
     * Get crossword items array
     */
    private function getItems()
    {
        $items = array();
        for ($i = 0; $i < count($this->grid->words); $i++)
        {
            $w =& $this->grid->words[$i];
            $items[] = array(
                "axis"      => $w->axis,
                "word"		=> $w->word,
                "question"	=> $this->getQuestion($w->word_id)
            );
        }

        return $items;
    }

    /**
     * Get question for the word
     */
    function getQuestion($word_id)
    {
        $question = CrossStringList::find()->select('question')->where(['id'=>$word_id])->one();

        return $question->question;
    }

    /**
     * Get crossword items
     * @return array
     */
    function getWords()
    {
        $result = array();
        foreach($this->items as $item){
           if($item['axis']==1)
               $result[1][]=$item;
           if($item['axis']==2)
               $result[2][]=$item;
        }
        return $result;
    }

    function getHtml(){
        return $this->grid->getHTML();
    }
}