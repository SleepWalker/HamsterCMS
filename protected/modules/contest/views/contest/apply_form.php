<?php
/**
 * @var  \contest\models\view\Request $model
 */
?>

<h1>Заявка на участие в конкурсе</h1>

<p class="note">Пожалуйста, перед тем как подавать заявку, ознакомьтесь с <?= CHtml::link('правилами конкурса', array('rules')) ?>.</p>

<div class="form form--inline">
<?php $form = $this->beginWidget('CActiveForm', array(
    'enableAjaxValidation' => true,
    'clientOptions' => array(
        'ajaxVar' => 'ajaxValidation',
        'validateOnSubmit' => true,
    ),
)); ?>

<div class="form__row form__row--inline">
    <?= $form->radioButtonList($model, 'type', array(
        'solo' => 'Соло',
        'group' => 'Группа',
    )); ?>
    <?= $form->error($model, 'type'); ?>
    <?php
    Yii::app()->clientScript->registerScript(__FILE__.'#group-solo-switch', '$(function() {
        $("#'.CHtml::activeId($model, 'type').'").change(function() {
            var selected = $("input:checked", this).val();
            var $solo = $(".js-solo-only");
            var $group = $(".js-group-only");
            $solo[selected == "solo" ? "show" : "hide"]();
            $group[selected == "group" ? "show" : "hide"]();
        }).change();
    })');
    ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'name'); ?>
    <?= $form->textField($model, 'name', array('class' => 'form__input')); ?>
    <?= $form->error($model, 'name'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'demos'); ?>
    <?= $form->textArea($model, 'demos', array('class' => 'form__input')); ?>
    <?= $form->error($model, 'demos'); ?>
    <p class="note">Вы можете бесплатно загрузить свои записи на <a href="http://yotube.com">youtube.com</a> или <a href="http://ex.ua">ex.ua</a></p>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'format'); ?>
    <?= $form->radioButtonList($model, 'format', array(
        'Сольное исполнение (без сопровождения)',
        'Сольное исполнение под минус',
        'Сольное исполнение с концертмейстером',
    ), array('class' => 'form__input')); ?>
    <?= $form->error($model, 'format'); ?>
    <p class="note">В случае исполнения с концертмейстером, укажите информацию о нем в форме "Исполнитель(-ли)"</p>
</div>

<fieldset>
    <legend>Исполнитель(-ли)</legend>

    <?php
    foreach ($model->musicians as $index => $musician) {
        ?>
        <div class="form__row">
            <?= $form->textField($musician, "[$index]first_name", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('first_name'),
            )); ?>
            <?= $form->textField($musician, "[$index]last_name", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('last_name'),
            )); ?>
            <?= $form->textField($musician, "[$index]email", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('email'),
            )); ?>
            <?php $this->widget('CMaskedTextField', array(
                'model' => $musician,
                'attribute' => "[$index]phone",
                'htmlOptions' => array(
                    'class' => 'form__input',
                    'placeholder' => $musician->getAttributeLabel('phone'),
                ),
                'mask' => '+38 (999) 999-99-99',
            )); ?>
            <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model' => $musician,
                'attribute' => "[$index]birthdate",
                'language' => 'ru',
                'htmlOptions' => array(
                    'class' => 'form__input',
                    'placeholder' => $musician->getAttributeLabel('birthdate'),
                ),
                'options' => array(
                    'changeYear' => true,
                    'changeMonth' => true,
                    'dateFormat' => 'dd.mm.yy',
                    'defaultDate' => '-18y',
                    'minDate' => '01.01.' . (date('Y')-70),
                    'maxDate' => '31.12.' . (date('Y')-7),
                    'yearRange' => (date('Y')-70).':'.(date('Y')-7),
                ),
            )); ?>
            <?= $form->textField($musician, "[$index]instrument", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('instrument'),
            )); ?>

            <?= $form->textField($musician, "[$index]school", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('school')
            )); ?>
            <?= $form->textField($musician, "[$index]class", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('class'),
            )); ?>
            <?= $form->textField($musician, "[$index]teacher", array(
                'class' => 'form__input',
                'placeholder' => $musician->getAttributeLabel('teacher'),
            )); ?>

            <?= $form->error($musician, "[$index]first_name"); ?>
            <?= $form->error($musician, "[$index]last_name"); ?>
            <?= $form->error($musician, "[$index]email"); ?>
            <?= $form->error($musician, "[$index]phone"); ?>
            <?= $form->error($musician, "[$index]birthdate"); ?>
            <?= $form->error($musician, "[$index]instrument"); ?>
            <?= $form->error($musician, "[$index]school"); ?>
            <?= $form->error($musician, "[$index]class"); ?>
            <?= $form->error($musician, "[$index]teacher"); ?>
        </div>
        <?php
    }
    ?>
</fieldset>

<fieldset>
    <legend>Исполняемые композиции</legend>

    <?php
    foreach ($model->compositions as $index => $composition) {
        ?>
            <div class="form__row">
                <?= $form->textField($composition, "[$index]author", array(
                    'class' => 'form__input',
                    'placeholder' => $composition->getAttributeLabel('author'),
                )); ?>
                <?= $form->textField($composition, "[$index]title", array(
                    'class' => 'form__input',
                    'placeholder' => $composition->getAttributeLabel('title'),
                )); ?>
                <?= $form->textField($composition, "[$index]duration", array(
                    'class' => 'form__input',
                    'placeholder' => $composition->getAttributeLabel('duration'),
                )); ?>
                <?= $form->error($composition, "[$index]author"); ?>
                <?= $form->error($composition, "[$index]title"); ?>
                <?= $form->error($composition, "[$index]duration"); ?>
            </div>
        <?php
    }
    ?>
</fieldset>

<div class="form__controls">
    <?= CHtml::submitButton('Отправить', array('class' => 'button')); ?>
</div>

<?php $this->endWidget('CActiveForm'); ?>
</div>
