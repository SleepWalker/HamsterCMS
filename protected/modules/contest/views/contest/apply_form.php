<?php
/**
 * @var  \contest\models\view\ApplyForm $model
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
    <div class="form__row__right--push">
        <?= $form->radioButtonList($model->request, 'type', array(
            'solo' => 'Соло',
            'group' => 'Группа',
        )); ?>
        <?= $form->error($model->request, 'type'); ?>
    </div>
    <?php
    Yii::app()->clientScript->registerScript(__FILE__.'#group-solo-switch', '$(function() {
        $("#'.CHtml::activeId($model->request, 'type').'").change(function() {
            var selected = $("input:checked", this).val();
            var $solo = $(".js-solo-only");
            var $group = $(".js-group-only");
            $solo[selected == "solo" ? "show" : "hide"]();
            $group[selected == "group" ? "show" : "hide"]();
        }).change();

        $(".js-add-row").click(function(event) {
            event.preventDefault();
            var $hidden = $(".js-addable").filter(":hidden");
            $hidden.eq(0).show();
            if ($hidden.length == 1) {
                $(this).hide();
            }
        });
    })');
    ?>
</div>

<div class="form__row js-group-only">
    <?= $form->labelEx($model->request, 'name'); ?>
    <?= $form->textField($model->request, 'name', array('class' => 'form__input')); ?>
    <?= $form->error($model->request, 'name'); ?>
</div>

<div class="form__row js-solo-only">
    <?= $form->labelEx($model->request, 'format'); ?>
    <div class="form__row__right">
        <?= $form->radioButtonList($model->request, 'format', $model->getFormatsList(), array(
            'class' => 'form__input',
        )); ?>
        <?= $form->error($model->request, 'format'); ?>
    </div>
</div>

<div class="form__row">
    <div class="form__row__left">
        <!-- <?= $form->labelEx($model, 'musicians'); ?> -->
        Контакты
    </div>
    <div class="form__row__right--push">
        <div class="form__row__small">
            <input class="form__input" style="width:33%;" placeholder="Ваше имя" type="text">
            <input class="form__input" style="width:33%;" placeholder="Email" type="email">
            <input class="form__input" style="width:33%;" placeholder="Телефон" type="text">
        </div>
    </div>
</div>

<div class="form__row">
    <div class="form__row__left">
        <?= $form->labelEx($model, 'musicians'); ?>
        <p>
            <button class="button js-add-row" type="button">Добавить</button>
        </p>
    </div>

    <div class="form__row__right--push">
        <?php
        foreach ($model->musicians as $index => $musician) {
            $isHidden = $index && $musician->isEmpty() && !$musician->hasErrors();
            ?>
            <div class="form__row js-addable"<?php if ($isHidden) echo ' style="display: none;"'; ?>>
                <div class="form__row__small">
                    <?= $form->textField($musician, "[$index]first_name", array(
                        'class' => 'form__input',
                        'style' => 'width: 40%;',
                        'placeholder' => $musician->getAttributeLabel('first_name'),
                    )); ?>
                    <?= $form->textField($musician, "[$index]last_name", array(
                        'class' => 'form__input',
                        'style' => 'width: 40%;',
                        'placeholder' => $musician->getAttributeLabel('last_name'),
                    )); ?>
                    <?= $form->textField($musician, "[$index]instrument", array(
                        'class' => 'form__input',
                        'style' => 'width: 19%;',
                        'placeholder' => $musician->getAttributeLabel('instrument'),
                    )); ?>
                </div>

                <div class="form__row__small">
                    <?= $form->textField($musician, "[$index]school", array(
                        'class' => 'form__input',
                        'style' => 'width: 40%;',
                        'placeholder' => $musician->getAttributeLabel('school')
                    )); ?>
                    <?= $form->textField($musician, "[$index]teacher", array(
                        'class' => 'form__input',
                        'style' => 'width: 40%;',
                        'placeholder' => $musician->getAttributeLabel('teacher'),
                    )); ?>
                    <?= $form->textField($musician, "[$index]class", array(
                        'class' => 'form__input',
                        'style' => 'width: 19%;',
                        'placeholder' => $musician->getAttributeLabel('class'),
                    )); ?>
                </div>

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
        <?= $form->error($model, 'musicians'); ?>
    </div>
</div>

<div class="form__row">
    <div class="form__row__left">
        <?= $form->labelEx($model, 'compositions'); ?>
    </div>

    <div class="form__row__right--push">
        <?php
        foreach ($model->compositions as $index => $composition) {
            ?>
            <div class="form__row">
                <?= $form->textField($composition, "[$index]author", array(
                    'class' => 'form__input',
                    'style' => 'width: 40%;',
                    'placeholder' => $composition->getAttributeLabel('author'),
                )); ?>
                <?= $form->textField($composition, "[$index]title", array(
                    'class' => 'form__input',
                    'style' => 'width: 40%;',
                    'placeholder' => $composition->getAttributeLabel('title'),
                )); ?>
                <?= $form->textField($composition, "[$index]duration", array(
                    'class' => 'form__input',
                    'style' => 'width: 19%;',
                    'placeholder' => $composition->getAttributeLabel('duration'),
                )); ?>
                <?= $form->error($composition, "[$index]duration"); ?>
            </div>
            <?php
        }
        ?>
        <?= $form->error($model, 'compositions'); ?>
    </div>
</div>

<div class="form__row">
    <?= $form->labelEx($model->request, 'demos'); ?>
    <?= $form->textArea($model->request, 'demos', array('class' => 'form__input')); ?>
    <?= $form->error($model->request, 'demos'); ?>
    <p class="note">
        Вы можете бесплатно загрузить свои записи на <a href="http://yotube.com">youtube.com</a>
        или <a href="http://ex.ua">ex.ua</a>.<br>
        Так же есть инструкция по
        <?= CHtml::link('загрузке видео на youtube', ['/page/view', 'id' => 'how-to-youtube'], ['target' => '_blank']); ?>.
    </p>
</div>

<div class="form__row">
    <?= $form->labelEx($model->request, 'demos'); ?>
    <?= $form->textArea($model->request, 'demos', ['class' => 'form__input']); ?>
    <?= $form->error($model->request, 'demos'); ?>
    <p class="note">
        Дополнительная информация
        Если вы играете с концертмейтером или живым сопровождением, укажите
        здесь информацию о нем, что бы наши ведущие могли правильно обьявить Ваш номер.
    </p>
</div>

<div class="form__controls">
    <?= CHtml::submitButton('Отправить', array('class' => 'button')); ?>
</div>

<?php $this->endWidget('CActiveForm'); ?>
</div>
