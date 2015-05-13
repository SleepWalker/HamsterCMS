<?php
/**
 * @var \contest\models\view\ConfirmForm $model
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
    <?php if ($request->type == $request::TYPE_GROUP): ?>
        <div class="form__row">
            <?= $form->labelEx($request, 'name'); ?>
            <?= $form->textField($request, 'name', array('class' => 'form__input')); ?>
            <?= $form->error($request, 'name'); ?>
        </div>
    <?php endif; ?>
    <div class="form__row">
        <div class="form__row__left">
            <?= $form->labelEx($request, 'musicians'); ?>
        </div>

        <div class="form__row__right--push">
            <?php
            foreach ($request->musicians as $index => $musician) {
                $isHidden = $musician->isEmpty();
                ?>
                <div class="form__row"<?php if ($isHidden) echo ' style="display: none;"'; ?>>
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
            <?= $form->error($request, 'musicians'); ?>
        </div>
    </div>

    <div class="form__row">
        <div class="form__row__left">
            <?= $form->labelEx($request, 'compositions'); ?>
        </div>

        <div class="form__row__right--push">
            <?php
            foreach ($request->compositions as $index => $composition) {
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
            <?= $form->error($request, 'compositions'); ?>
        </div>
    </div>
</div>

<div class="form">
    <div class="form__row">
        <?= $form->checkBox($model, 'needSoundcheck') ?>
        <?= $form->labelEx($model, 'needSoundcheck') ?>
        <?= $form->error($model, 'needSoundcheck') ?>
    </div>
    <div class="form__row">
        <?= $form->checkBox($model, 'hasMinus') ?>
        <?= $form->labelEx($model, 'hasMinus') ?>
        <?= $form->error($model, 'hasMinus') ?>
        <div class="hint">Для экономии нашего и вашего времени, пожалуйста, заранее отправьте минус на нашу почту <a href="mailto:contest@estrocksection.kiev.ua">contest@estrocksection.kiev.ua</a>. Спасибо!</div>
    </div>
    <div class="form__row">
        <?= $form->checkBox($model, 'willInviteFriends') ?>
        <?= $form->labelEx($model, 'willInviteFriends') ?>
        <?= $form->error($model, 'willInviteFriends') ?>
    </div>
    <div class="form__controls">
        <?= \CHtml::submitButton('Отправить', ['class' => 'button']) ?>
    </div>
</div>

<?php $this->endWidget('CActiveForm'); ?>

