<?php


namespace frontend\components;

use frontend\models\CrossCell;
use frontend\models\CrossGrid;
use frontend\models\CrossWord;
use Yii;
use frontend\models\CrossStringList;
use frontend\models\CrossTempList;
use common\models\CrossGlobalsVariables;
use frontend\components\CrossGridActions;
use yii\db\Expression;

class CrossBuilder
{
    public $grid;
    public $category;
    public $section;
    public $max_full_tries = 1;
    public $max_words = 15;
    public $items;
    public $max_tries = 15;

    public $match_line;
    public $full_tries = 0;
    public $tries = 0;

    public $crossid = 0;

    /**
     * Construct
     */
    function __construct($category, $section, $max_words)
    {
        $this->category = $category;
        $this->section = $section;
        $this->max_words = $max_words;
    }

    /**
     * Build crossword
     */
    public function buildCross()
    {
        $this->createCrossID();
        $words = $this->getWordsList();
        $cross = $this->generateFromWords($words);

        return $this->grid->grid_id;
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
    private function getWordslist()
    {
        $words = CrossStringList::find()
            ->select('id')
            ->where(['id_category' => $this->category, 'id_section'=>$this->section])
            ->orderBy(new Expression('rand()'))
            ->limit($this->max_words*2)
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
        $max_words = $this->max_words;
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
        $this->setMaxWords($max_words);

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

        return true;
    }

    /**
     * Reset grid
     */
    private function resetGrid()
    {
        $this->grid = new CrossGridActions($this->crossid,40,30,$this->category, $this->section);
        $this->tries = 0;
        $this->items = 0;
    }

    /**
     * Place first word to the cell
     */
    private function placeFirstWord()
    {
        $word = $this->getRandomWord();
        $x = $this->grid->getCenterPos(CrossGlobalsVariables::CROSS_VERTIKAL);
        $y = $this->grid->getCenterPos(CrossGlobalsVariables::CROSS_HORIZONTAL,$word->value);
        $this->grid->placeWord($word->value, $word->id, $x, $y, CrossGlobalsVariables::CROSS_VERTIKAL);
    }

    /**
     * Try to generate crossword automatically
     */
    private function autoGenerate()
    {

        while ($this->grid->countWords() < $this->max_words && $this->tries < $this->max_tries)
        {
            $this->tries++;

            $w = $this->grid->getRandomWord();

            if (is_int($w))
                if ($w == CrossGlobalsVariables::WORDS_FULLY_CROSSED)
                {
                    break;
                }
            $axis = $w->getCrossAxis();
            $cells = $w->getCrossableCells($this->grid);
            while (count($cells))
            {

                $n = array_rand($cells);
                $cell = $cells[$n];
                $list = $this->getWordWithStart($cell, $axis);
                $word = $list[0];
                $start = $list[1];

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

        $words = CrossTempList::find()->select('wordid')->where(['groupid'=>$this->crossid])->all(); //->offset($n)
        $search = array();
        foreach($words as $word){
            $search[]=$word->wordid;
        }
        $word = CrossStringList::find()->where(['in', 'id', $search])->orderBy(['length'=>SORT_DESC])->one();

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
    private function getWordWithStart($cell, $axis)
    {

        $start = $this->grid->getStartCell($cell, $axis);
        $end = $this->grid->getEndCell($cell, $axis);
        $word = $this->getWord($cell, $start, $end, $axis);

        if (!$word['value'])
            return false;

        $pos = false;
        $can = false;

        while ($can!=false);
        {
            $s_cell = $this->calcStartCell($cell, $start, $end, $axis, $word['value'], $pos);
            if($s_cell){
                $s_cell = CrossCell::findOne($s_cell->cell_id);

                $can = $this->grid->canPlaceWord($word['value'], $s_cell->x, $s_cell->y, $axis);
                if($can)
                    $can=false;
            }

        }

        return array($word, $s_cell);
    }

    /**
     * Try to get the word
     */
    private function getWord($cell, $start, $end, $axis)
    {
        $this->match_line = $this->getMatchLine($cell, $start, $end, $axis);
        $match = $this->getMatchLike($this->match_line);
        $min = $this->getMatchMin($this->match_line);
        $max = mb_strlen($this->match_line);
        $regexp = $this->getMatchRegexp($this->match_line);

        $rs = $this->loadWords($match, $min, $max);

        shuffle($rs);
        $word = '';
        $words = array();


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
    private function getMatchLine($cell, $start, $end, $axis)
    {
        $starts = microtime(true);
        $start = CrossCell::findOne($start->cell_id);
        $end = CrossCell::findOne($end->cell_id);
        $x = $start->x;
        $y = $start->y;
        $str = '';

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $max = $end->x;
            $range = range($x,$max);
            $cells = CrossCell::find()->where(['y'=>$y, 'grid_id'=>$this->grid->grid_id ])->andWhere(['in','x',$range])->all();
            foreach($cells as $cell)
            {
                $str.= mb_strlen($cell->letter) ? $cell->letter : '_';
            }
        }
        else
        {
            $max = $end->y;
            $range = range($y,$max);
            $cells = CrossCell::find()->where(['x'=>$x, 'grid_id'=>$this->grid->grid_id ])->andWhere(['in','y',$range])->all();
            foreach($cells as $cell)
            {
                $str.= mb_strlen($cell->letter) ? $cell->letter : '_';
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
        $str = preg_replace_callback("/^_*/", function($m) { return "^.{0,".strlen($m[0])."}"; }, $str, 1);
        $str = preg_replace_callback("/_*$/", function($m) { return ".{0,".strlen($m[0])."}$"; }, $str, 1);
        $str = preg_replace_callback("/_+/", function($m) { return ".{".strlen($m[0])."}"; }, $str);

        return $str;
    }

    /**
     * Load words for the match
     */
    private function loadWords($match, $len_min, $len_max)
    {
        $used_words = $this->getUsedWords();
        $crosstmplist = CrossTempList::find()->select('wordid')->where(['groupid'=>$this->crossid])->andWhere(['not in','wordid',$used_words])->all();
        $arr = array();
        foreach($crosstmplist as $val){
            $arr[] = $val->wordid;
        }

        $result = CrossStringList::find()->select(['id', 'value'])->where(['in','id',$arr])->andWhere(['between','length',$len_min,$len_max])->andWhere(['like','value',$match,false])->asArray()->all();

//        die;
//        $sql = "SELECT id, value FROM cross_string_list WHERE
//                  id IN (SELECT wordid FROM cross_temp_list WHERE wordid NOT IN (:words) AND groupid=:groupid)
//                  AND length BETWEEN :len_min AND :len_max AND value LIKE :match";
//        $values = array(":words"=>implode(',',$used_words), ":groupid"=>$this->crossid, ":len_min"=>$len_min, ":len_max"=>$len_max, ":match"=>$match);
//        $connection = Yii::$app->getDb();
//        $command = $connection->createCommand($sql, $values);
//        $result = $command->queryAll();
//        var_dump($result);
//        die;
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
    function calcStartCell($cell, $start, $end, $axis, $word, $pos)
    {
        $cell = CrossCell::findOne($cell->cell_id);
        $start = CrossCell::findOne($start->cell_id);
        $end = CrossCell::findOne($end->cell_id);
        $x = $cell->x;
        $y = $cell->y;

        if ($axis == CrossGlobalsVariables::CROSS_HORIZONTAL)
        {
            $s = $cell->x - $start->x;
            $e = $end->x - $cell->x;
            $l = mb_strlen($word);
            do
            {
                $offset = isset($pos) ? $pos+1 : 0;
                @$pos = mb_strpos($word, $cell->letter, $offset);
                $a = $l-$pos-1;
                if ($pos <= $s && $a <= $e)
                {
                    $x-= $pos;
                    return $this->grid->cells[$x][$y];
                }
            }
            while ($pos !== false);

            return false;

        }
        else
        {
            $s = $cell->y - $start->y;
            $e = $end->y - $cell->y;
            $l = mb_strlen($word);

            do
            {
                $offset = isset($pos) ? $pos+1 : 0;
                $pos = mb_strpos($word, $cell->letter, $offset);
                $a = $l-$pos-1;
                if ($pos <= $s && $a <= $e)
                {
                    $y-= $pos;
                    return $this->grid->cells[$x][$y];
                }
            }
            while ($pos !== false);

            return false;
        }

    }


    /**
     * Get crossword items array
     */
    function getItems()
    {
        $items = array();
        foreach ($this->grid->words as $val)
        {
            $word = $val;
            $w = CrossWord::findOne($word->id);
            $items[] = array(
                "axis"      => $w->axis,
                "word"		=> $this->getValue($w->word_id),
                "question"	=> $this->getQuestion($w->word_id),
                "x"			=> $w->x + 1,
                "y"			=> $w->y + 1,
            );
        }

        return $items;
    }

    /**
     * Get value for the word
     */
    function getValue($word_id)
    {
        $question = CrossStringList::find()->select('value')->where(['id'=>$word_id])->one();

        return $question->value;
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
        $words = $this->getItems();
        foreach($words as $item){
            if($item['axis']==1)
                $result[1][]=$item;
            if($item['axis']==2)
                $result[2][]=$item;
        }
        return $result;
    }

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
        $count = 0;
        for ($x = 0; $x < $grid->cols; $x++)
        {
            $css = '';
            $count = 0;
            $tr = '';
            for ($y = 0; $y < $grid->rows; $y++)
            {
                $class = $cell[$x][$y]->letter!='' ? 'cellLetter' : 'cellEmpty';
                if($class=='cellEmpty')
                    $count+=1;
                $color = "white";
//                    $class = 'cellDebug';
                $tr .= "\n";
                $tempinum = '';
                if ($cell[$x][$y]->number!=0) {
                    $tempinum = $cell[$x][$y]->number;
                }
                if ($y == 0)
                    $tr.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif ($x == 0)
                    $tr.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
                elseif ($cell[$x][$y]->letter!='')
                {
                    if ($fillflag) {
                        $letter=$cell[$x][$y]->letter;
                    } else {
                        $letter="&nbsp;";
                    }
                    $tr.= "<td bgcolor='".$color."' class='".$class.$cellflag."'><sup style=\"position: absolute; font-size: 75%;line-height: normal;top: initial;\">$tempinum</sup><span class='letter'><input type='text' size='1' value='$letter'></span><span class='field'><input type='text' size='1'></span></td>";
                }
                else
                    $tr.= "<td bgcolor='".$color."' class='".$class.$cellflag."'>&nbsp;</td>";
            }
            if($count==$grid->rows)
                $css = 'style="display:none;"';
            $html.= "<tr align='center' $css >".$tr;
            $html.= "</tr>";
        }

        $html.= "</table>";

        return $html;
    }
}