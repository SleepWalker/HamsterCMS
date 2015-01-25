<?php
/**
 * @var  \contest\models\ApplyForm $model
 */
?>

<h1>Заявка на участие в конкурсе</h1>

<p>Пожалуйста, перед тем как подавать заявку, ознакомьтесь с <?= CHtml::link('правилами конкурса', 'rules') ?>.</p>

<div class="form">
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
    <?= $form->labelEx($model, 'first_name', array(
        'class' => 'js-solo-only',
    )); ?>
    <?= $form->labelEx($model, 'first_name', array(
        'class' => 'js-group-only',
        'label' => 'Название группы',
    )); ?>
    <?= $form->textField($model, 'first_name'); ?>
    <?= $form->error($model, 'first_name'); ?>
</div>

<div class="form__row js-solo-only">
    <?= $form->labelEx($model, 'last_name'); ?>
    <?= $form->textField($model, 'last_name'); ?>
    <?= $form->error($model, 'last_name'); ?>
</div>

<div class="form__row js-solo-only">
    <?= $form->labelEx($model, 'birthdate'); ?>
    <?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
        'model' => $model,
        'attribute' => 'birthdate',
        'language' => 'ru',
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
    <?= $form->error($model, 'birthdate'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'email'); ?>
    <?= $form->emailField($model, 'email'); ?>
    <?= $form->error($model, 'email'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'phone'); ?>
    <?php $this->widget('CMaskedTextField', array(
        'model' => $model,
        'attribute' => 'phone',
        'mask' => '+38 (999) 999-99-99',
    )); ?>
    <?= $form->error($model, 'phone'); ?>
</div>

<div class="form__row js-solo-only">
    <?= $form->labelEx($model, 'instrument'); ?>
    <?= $form->textField($model, 'instrument'); ?>
    <?= $form->error($model, 'instrument'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'school'); ?>
    <?= $form->textField($model, 'school'); ?>
    <?= $form->error($model, 'school'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'teacher'); ?>
    <?= $form->textField($model, 'teacher'); ?>
    <?= $form->error($model, 'teacher'); ?>
</div>

<div class="form__row">
    <?= $form->labelEx($model, 'demos'); ?>
    <?= $form->textArea($model, 'demos'); ?>
    <?= $form->error($model, 'demos'); ?>
</div>

<div class="form__controlls">
    <?= CHtml::submitButton('Отправить'); ?>
</div>

<?php $this->endWidget('CActiveForm'); ?>
</div>
