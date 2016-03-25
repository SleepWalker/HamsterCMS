<?php
/**
 * @var \contest\models\Musician[] $musicians
 */
?>
    <?php if(!empty($request->name)): ?>
        <h3><?= CHtml::encode($request->name); ?></h3>
    <?php endif; ?>

    <p>
        <?= CHtml::encode($request->contact_name); ?>
        <?= CHtml::encode($request->contact_email); ?>
        <?= CHtml::encode($request->contact_phone); ?>
    </p>

<?php
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
