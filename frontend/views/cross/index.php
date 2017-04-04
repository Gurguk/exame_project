<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<style>

</style>
<?php $this->registerJsFile('/frontend/web/js/cross.js',  ['depends' => [
    'yii\web\YiiAsset',
]]);

?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Crossword generator!</h1>

        <?php echo $grid; ?>
        <table border=1 align="center" class="question-table">
            <tr class="table-header">
                <th class="number">№</th>
                <th class="question">Вопрос</th>
            </tr>
            <tr class="table-gorizontal-header"><td colspan="2" class="gorizontal">По горизонтали</td></tr>
            <?php foreach ($cross[2] as $key=>$word): ?>
                <tr class="table-gorizontal">
                    <td class="number"><?=$key+1?>.</td>
                    <td class="question" align="left"><?=$word['question']?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="table-vertical-header"><td colspan="2" class="vertical">По вертикали</td></tr>
            <?php foreach ($cross[1] as $key=>$word): ?>
                <tr class="table-vertical">
                    <td class="number"><?=$key+1?>.</td>
                    <td class="question" align="left"><?=$word['question']?></td>
                </tr>
            <?php endforeach; ?>

        </table>
        <button class="show_letter">Слова</button>
    </div>

</div>
