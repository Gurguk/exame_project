<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';


    $site_url = 'http://www.kotvet.ru/odn/1';
    $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';;
    phpinfo();
die;
    $site_ch = curl_init();
    curl_setopt($site_ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($site_ch, CURLOPT_URL,$site_url);
    curl_setopt($site_ch, CURLOPT_FAILONERROR, true);
    curl_setopt($site_ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($site_ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($site_ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($site_ch, CURLOPT_TIMEOUT, 60);
    $data = curl_exec($site_ch);

    $data =iconv( 'WINDOWS-1251', 'UTF-8',($data));

    preg_match_all ('~<br>По горизонтали:(.*?)<br><br>~is', $data, $words);
    $words = explode('<br>',$words[1][0]);
    foreach($words as $line){
        if(trim($line)=='')
            continue;
        preg_match('~<b>(.*?)</b>~is',$line,$word);
        preg_match("/[A-Za-zА-Яа-яЁё]{1,}$/ui", $word[1], $w);
        preg_match('~</b> - (.*?)$~is',$line,$question);
        var_dump($w[0],$question[1]);
    }

?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a></p>
    </div>

</div>
