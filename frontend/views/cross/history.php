<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';



?>
<div class="site-index">

    <div class="jumbotron">
        <ul>
       <?php foreach($data as $crossword):?>
            <li><a href="index.php?r=cross/show&cross_id=<?php echo $crossword->id; ?>">Кроссворд №<?php echo $crossword->id; ?></a></li>
       <?php endforeach; ?>
        </ul>
    </div>

</div>
