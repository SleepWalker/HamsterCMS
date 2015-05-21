<?php
/**
 */
?>

<?= \CHtml::beginForm() ?>
<p><?= \CHtml::submitButton('Разослать письма подтверждения', ['name' => 'sendConfirm']) ?></p>
<?= \CHtml::endForm() ?>


<?= \CHtml::beginForm() ?>
<h2>Отправить произвольный текст</h2>
<?php $this->widget('\ext\markitup\HMarkitupWidget', [
    'name' => 'html',
]); ?>
<p><?= \CHtml::submitButton('Отправить', ['name' => 'custom']) ?></p>
<?= \CHtml::endForm() ?>
