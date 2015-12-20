<?php
/**
 */
?>

<?= \CHtml::beginForm() ?>
<p><?= \CHtml::submitButton('Разослать письма подтверждения', ['name' => 'sendConfirm']) ?></p>
<?= \CHtml::endForm() ?>


<div class="form">
<?= \CHtml::beginForm('', 'post', [
    'id' => 'js-mailing-form',
]) ?>
    <h2>Отправить произвольный текст</h2>

    <fieldset>
        <legend>Фильтр получателей</legend>
        <div class="form__row">
            <div class="form__label">Тип заявки:</div>
            <?= \CHtml::radioButtonList('requestType', 'any', [
                'any' => 'Все',
                'notConfirmed' => 'Не подтвердженные',
                'accepted' => 'Принятые',
            ]) ?>
        </div>

        <div class="form__row">
            <div class="form__label">Формат номера:</div>
            <?= \CHtml::radioButtonList('type', 'any', [
                'any' => 'Любой',
                'group' => 'Группа',
                'solo' => 'Соло',
            ]) ?>
        </div>

        <div class="form__row">
            <div class="form__label">Отправить на заданный Email:</div>
            <?= \CHtml::textField('toEmail', '', [
                'placeholder' => 'Email',
            ]) ?>
        </div>
    </fieldset>

    <div class="form__row">
        <?= \CHtml::textField('subject', '', [
            'placeholder' => 'Тема письма',
        ]) ?>
    </div>

    <div class="form__row">
        <?php $this->widget('\ext\markitup\HMarkitupWidget', [
            'name' => 'message',
        ]); ?>
    </div>

    <p>
        <?= \CHtml::submitButton('Отправить', [
            'id' => 'js-send-custom',
            'name' => 'sendCustom',
        ]) ?>
        <?= \CHtml::submitButton('Предпросмотр', [
            'id' => 'js-send-preview',
            'name' => 'sendPreview',
        ]) ?>
    </p>
<?= \CHtml::endForm() ?>
</div>

<iframe name="preview" style="width:100%;height:600px;background: #fff;"></iframe>

<?php
\Yii::app()->clientScript->registerScript(__FILE__, <<<SCRIPT
    $(function() {
        $('#js-send-custom').on('click', function(event) {
            $('#js-mailing-form').attr('target', '');

            if (!confirm('Вы уверены?')) {
                event.preventDefault();
            }
        });
        $('#js-send-preview').on('click', function() {
            $('#js-mailing-form').attr('target', 'preview');
        });
    });
SCRIPT
);
?>
