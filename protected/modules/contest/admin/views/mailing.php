<?php
/**
 */
?>

<?= \CHtml::beginForm() ?>
<p><?= \CHtml::submitButton('Разослать письма подтверждения', ['name' => 'sendConfirm']) ?></p>
<?= \CHtml::endForm() ?>


<?= \CHtml::beginForm() ?>
<h2>Отправить произвольный текст</h2>

<?= \CHtml::textField('subject', '', [
    'placeholder' => 'Тема письма',
]) ?>

<?php $this->widget('\ext\markitup\HMarkitupWidget', [
    'name' => 'message',
]); ?>

<p><?= \CHtml::submitButton('Отправить', ['name' => 'sendCustom']) ?></p>
<?= \CHtml::endForm() ?>
