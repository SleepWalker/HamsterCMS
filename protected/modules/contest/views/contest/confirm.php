<?php
/**
 * @var \contest\models\view\ConfirmForm $confirmForm
 * @var \contest\models\view\ApplyForm $applyForm
 */
?>

<h1>Страница финалиста</h1>
<p>Поздравляем, вы только что подтвердили свое участие в финале конкурса «Рок єднає нас» 2015. Прежде чем идти отмечать этот праздник, будьте добры, перепроверьте ваши данные:</p>

<?php $form = $this->beginWidget('CActiveForm', array(
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
)); ?>

<div class="form form--inline">
    <?php if ($applyForm->request->type == \contest\models\Request::TYPE_GROUP): ?>
        <div class="form__row">
            <?= $form->labelEx($applyForm->request, 'name'); ?>
            <?= $form->textField($applyForm->request, 'name', array('class' => 'form__input')); ?>
            <?= $form->error($applyForm->request, 'name'); ?>
        </div>
    <?php endif; ?>
    <div class="form__row">
        <div class="form__row__left">
            <?= $form->labelEx($applyForm, 'musicians'); ?>
        </div>

        <div class="form__row__right--push">
            <?php
            foreach ($applyForm->musicians as $index => $musician) {
                $isHidden = $musician->isEmpty();
                ?>
                <div class="form__row"<?= $isHidden ? ' style="display: none;"' : ''; ?>>
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
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $musician,
                            'attribute' => "[$index]birthdate",
                            'language' => 'ru',
                            'htmlOptions' => array(
                                'class' => 'form__input',
                                'style' => 'width: 19%;',
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
                    </div>

                    <?= $form->error($musician, "[$index]first_name"); ?>
                    <?= $form->error($musician, "[$index]last_name"); ?>
                    <?= $form->error($musician, "[$index]birthdate"); ?>
                </div>
                <?php
            }
            ?>
            <?= $form->error($applyForm, 'musicians'); ?>
        </div>
    </div>

    <div class="form__row">
        <div class="form__row__left">
            <?= $form->labelEx($applyForm, 'compositions'); ?>
        </div>

        <div class="form__row__right--push">
            <?php
            foreach ($applyForm->compositions as $index => $composition) {
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
            <?= $form->error($applyForm, 'compositions'); ?>
        </div>
    </div>
</div>

<div class="form">
    <div class="form__row">
        <?= $form->checkBox($confirmForm, 'needSoundcheck') ?>
        <?= $form->labelEx($confirmForm, 'needSoundcheck') ?>
        <?= $form->error($confirmForm, 'needSoundcheck') ?>
    </div>
    <div class="form__row">
        <?= $form->checkBox($confirmForm, 'hasMinus') ?>
        <?= $form->labelEx($confirmForm, 'hasMinus') ?>
        <?= $form->error($confirmForm, 'hasMinus') ?>
        <div class="hint">Для экономии нашего и вашего времени, пожалуйста, заранее отправьте минус на нашу почту <a href="mailto:contest@estrocksection.kiev.ua">contest@estrocksection.kiev.ua</a>. Спасибо!</div>
    </div>
    <div class="form__row">
        <?= $form->checkBox($confirmForm, 'willInviteFriends') ?>
        <?= $form->labelEx($confirmForm, 'willInviteFriends') ?>
        <?= $form->error($confirmForm, 'willInviteFriends') ?>
    </div>
    <div class="form__controls">
        <?= \CHtml::hiddenField(\CHtml::modelName($applyForm) . '[submitted]', 1) ?>
        <?= \CHtml::submitButton('Отправить', ['class' => 'button']) ?>
    </div>
</div>

<?php $this->endWidget('CActiveForm'); ?>

