<?php
/**
 * @var \contest\models\Musician[] $musicians
 */
foreach ($musicians as $musician) {
    ?>
    <p>
        <?= CHtml::encode($musician->first_name); ?>
        <?= CHtml::encode($musician->last_name); ?>,
        <?= CHtml::encode($musician->birthdate); ?>
        <br>
        <?= CHtml::encode($musician->email); ?>
        <?= CHtml::encode($musician->phone); ?>
        <br>
        <b><?= CHtml::encode($musician->instrument); ?></b> /
        <?= CHtml::encode($musician->school); ?> /
        <?= CHtml::encode($musician->class); ?> /
        <?= CHtml::encode($musician->teacher); ?>
    </p>
    <?php
}
