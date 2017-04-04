<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
//var_dump($categories);
?>
<?php $this->registerJsFile('/frontend/web/js/cross.js',  ['depends' => [
    'yii\web\YiiAsset',
]]);

?>
<?php $form = ActiveForm::begin(['action' =>['cross/index'], 'id' => 'forum_post', 'method' => 'post',]); ?>
<label>Категория</label>
<br>
<?= Html::activeDropDownList($model, 'id', $categories,['prompt'=>'Выберите категорию']) ?>
<br><label>Раздел</label>
<div id="section"></div>

<div class="form-group">
    <?= Html::submitButton('Создать кроссворд', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>