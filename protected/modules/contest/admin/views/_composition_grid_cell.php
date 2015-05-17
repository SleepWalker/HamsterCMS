<?php
/**
 * @var \contest\models\Composition[] $compositions
 */
foreach ($compositions as $composition) {
    ?>
    <p>
        <?= CHtml::encode($composition->getFullName()); ?>
        (<?= CHtml::encode($composition->duration); ?>мин)
    </p>
    <?php
}
