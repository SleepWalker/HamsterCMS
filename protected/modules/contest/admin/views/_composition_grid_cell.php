<?php
/**
 * @var \contest\models\Composition[] $compositions
 */
foreach ($compositions as $composition) {
    ?>
    <p>
        <?= CHtml::encode($composition->author); ?> —
        <?= CHtml::encode($composition->title); ?>
        (<?= CHtml::encode($composition->duration); ?>мин)
    </p>
    <?php
}
