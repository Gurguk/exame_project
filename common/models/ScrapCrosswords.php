<?php
namespace common\models;

use Yii;
use yii\base\Model;
use frontend\models\CrossStringList;

/**
 * Scraper
 */
class ScrapCrosswords extends Model
{

    public function scrapWords($start,$end,$resource)
    {
        $result = array();
        for($i=$start; $i<=$end; $i++)
        {
            $data = $this->Request($resource.$i);
            $result[] = $this->GetWordsH($data);
            $result[] = $this->GetWordsV($data);
        }
        $model = new CrossStringList();
        $model->addDemo($result);
    }

    function Request($resource)
    {
        $site_url = $resource;
        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';
        $site_ch = curl_init();
        curl_setopt($site_ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($site_ch, CURLOPT_URL,$site_url);
        curl_setopt($site_ch, CURLOPT_FAILONERROR, true);
        curl_setopt($site_ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($site_ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($site_ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($site_ch, CURLOPT_TIMEOUT, 60);
        $site_html= curl_exec($site_ch);

        return iconv( 'WINDOWS-1251', 'UTF-8',$site_html);
    }

    function GetWordsH($data)
    {
        $result = array();
        preg_match_all ('~<br>По горизонтали:(.*?)<br><br>~is', $data, $words);
        $words = explode('<br>',$words[1][0]);
        foreach($words as $line){
            if(trim($line)=='')
                continue;
            preg_match('~<b>(.*?)</b>~is',$line,$word);
            preg_match("/[A-Za-zА-Яа-яЁё]{1,}$/ui", $word[1], $w);
            preg_match('~</b> - (.*?)$~is',$line,$question);
            $result[] = array('word'=>$w[0], 'question'=>$question[1]);
        }

        return $result;
    }

    function GetWordsV($data)
    {
        $result = array();
        preg_match_all('~<br>По вертикали:(.*?)</font>~is', $data, $words);
        $words = explode('<br>',$words[1][0]);
        foreach($words as $line){
            if(trim($line)=='')
                continue;
            preg_match('~<b>(.*?)</b>~is',$line,$word);
            preg_match("/[A-Za-zА-Яа-яЁё]{1,}$/ui", $word[1], $w);
            preg_match('~</b> - (.*?)$~is',$line,$question);
            $result[] = array('word'=>$w[0], 'question'=>$question[1]);
        }

        return $result;
    }
}