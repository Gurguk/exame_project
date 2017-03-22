<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<style>
    body, td { font-family: Courier; font-size: 10pt; }
    .crossTable { border-spacing:0px;  border-collapse: collapse; }
    .cellEmpty {  padding: 0px; }
    .cellNumber { padding: 1px; background-color: #FFFFFF; border: 0px solid #000000; width: 20px; height: 20px; }
    .cellLetter { padding: 1px; background-color: #EEEEEE; border: 1px solid #000000; width: 20px; height: 20px; }
    .crossTableA { border-spacing:0px;  border-collapse: collapse; }
    .cellEmptyA {  padding: 0px; }
    .cellNumberA { padding: 1px; background-color: #FFFFFF; border: 0px solid #000000; width: 30px; height: 30px; }
    .cellLetterA { padding: 1px; background-color: #EEEEEE; border: 1px solid #000000; width: 30px; height: 30px; }
    .cellDebugA { padding: 1px; border: 1px solid #000000; width: 30px; height: 30px; }
    .letter{ display: none;}
    .field input {
        border: 0;
        width: 27px;
        height: 27px;
        background: #eeeeee;
        text-align: center;
        outline: none;
    }
</style>
<?php $this->registerJsFile('/frontend/web/js/cross.js',  ['depends' => [
    'yii\web\YiiAsset',
]]); ?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Crossword generator!</h1>

        <?php echo $grid; ?>
        <table border=1 align="center">
            <tr>
                <th>№</th>
                <th>Вопрос</th>
            </tr>
            <tr><td colspan="2">По горизонтали</td></tr>
<!--            --><?php //foreach ($cross[1] as $key=>$word): ?>
<!--                <tr>-->
<!--                    <td>--><?//=$key+1?><!--.</td>-->
<!--                    <td align="left">--><?//=$word["question"]?><!--</td>-->
<!--                </tr>-->
<!--            --><?php //endforeach; ?>
<!--            <tr><td colspan="2">По вертикали</td></tr>-->
<!--            --><?php //foreach ($cross[2] as $key=>$word): ?>
<!--                <tr>-->
<!--                    <td>--><?//=$key+1?><!--.</td>-->
<!--                    <td align="left">--><?//=$word["question"]?><!--</td>-->
<!--                </tr>-->
<!--            --><?php //endforeach; ?>

        </table>
        <button class="show_letter">Слова</button>
    </div>

</div>
