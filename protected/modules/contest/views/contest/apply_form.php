<?php
/**
 * @var \contest\models\view\ApplyForm $model
 * @var bool $isContest whether this is a contest
 */

use contest\models\Request;
?>

<div class="form form--inline">
<?php $form = $this->beginWidget('CActiveForm', [
    'enableAjaxValidation' => true,
    'clientOptions' => [
        'ajaxVar' => 'ajaxValidation',
        'validateOnSubmit' => true,
    ],
]); ?>

<?php if ($isContest ?? true): ?>
    <div class="form__row">
        <div class="form__row__right--push">
            <h1>Заява на участь у конкурсі</h1>
        </div>

        <p class="note">
            Будь ласка, перед тим, як подавати заяву, ознайомтесь
            з <?= \CHtml::link('правилами конкурсу', ['rules']) ?>.<br>
            Зверніть увагу, що заява заповнюється українською мовою.
        </p>

        <!-- TODO
        <p class="note">
            Якщо ви викладач, <?= \CHtml::link('зареєструйтесь', [\Yii::app()->user->registerUrl]) ?>
            у нас на сайті та керуйте заявами усіх учнів з вашого особистого кабінету.
        </p>
        -->
    </div>
<?php else: ?>
    <div class="form__row">
        <div class="form__row__right--push">
            <h1>Заява на участь у фестивалі</h1>
        </div>

        <p class="note">
            Будь ласка, перед тим, як подавати заяву, ознайомтесь
            з <?= \CHtml::link('правилами фестивалю', ['fest-rules']) ?>.<br>
            Зверніть увагу, що заява заповнюється українською мовою.
        </p>
    </div>
<?php endif; ?>

<?php
// TODO: if teacher is authorized - show him link to his cabinet
Yii::app()->clientScript->registerScript(__FILE__.'#group-solo-switch', '$(function() {
    $("#'.\CHtml::activeId($model->request, 'format').'").change(function() {
        var selected = parseInt($(this).val(), 10);
        var $solo = $(".js-solo-only");
        var $group = $(".js-group-only");

        $solo.toggle(selected !== ' . \CJavaScript::encode(Request::FORMAT_GROUP) . ');
        $group.toggle(selected === ' . \CJavaScript::encode(Request::FORMAT_GROUP) . ');
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

<div class="form__row">
    <?= $form->labelEx($model, 'nomination'); ?>

    <div class="form__row__right">
        <?= $form->dropDownList($model->request, 'format', $model->request->getFormatsList(), [
            'empty' => '-- формат номеру --'
        ]) ?>
        <?= $form->dropDownList($model->request, 'age_category', $model->request->getAgeCategoriesList(), [
            'empty' => '-- вікова категорія --'
        ]) ?>

        <?= $form->error($model->request, 'format'); ?>
        <?= $form->error($model->request, 'age_category'); ?>
    </div>
</div>

<div class="form__row js-group-only">
    <?= $form->labelEx($model->request, 'name'); ?>
    <?= $form->textField($model->request, 'name', ['class' => 'form__input']); ?>
    <?= $form->error($model->request, 'name'); ?>
</div>

<div class="form__row">
    <div class="form__row__left">
        <?= $form->labelEx($model, 'contacts'); ?>
    </div>
    <div class="form__row__right--push">
        <div class="form__row form__row--stretch">
            <?= $form->textField($model->request, 'contact_name', [
                'class' => 'form__input',
                'placeholder' => $model->request->getAttributeLabel('contact_name'),
            ]); ?>
            <?= $form->textField($model->request, 'contact_email', [
                'class' => 'form__input',
                'placeholder' => $model->request->getAttributeLabel('contact_email'),
            ]); ?>
            <?php $this->widget('CMaskedTextField', [
                'model' => $model->request,
                'attribute' => 'contact_phone',
                'htmlOptions' => [
                    'class' => 'form__input',
                    'placeholder' => $model->request->getAttributeLabel('contact_phone'),
                ],
                'mask' => '+38 (999) 999-99-99',
            ]); ?>
        </div>

        <?= $form->error($model->request, 'contact_name'); ?>
        <?= $form->error($model->request, 'contact_email'); ?>
        <?= $form->error($model->request, 'contact_phone'); ?>
    </div>
</div>

<div class="form__row">
    <div class="form__row__left">
        <?= $form->labelEx($model, 'musicians'); ?>
        <p>
            <button class="button js-add-row" type="button">Додати</button>
        </p>
    </div>

    <div class="form__row__right--push">
        <?php
        foreach ($model->musicians as $index => $musician) {
            $isHidden = $index && $musician->isEmpty() && !$musician->hasErrors();
            ?>
            <div class="form__row js-addable"<?= $isHidden ? ' style="display: none;"' : '' ?>>
                <div class="form__row__small form__row--stretch">
                    <?= $form->textField($musician, "[$index]first_name", [
                        'class' => 'form__input',
                        'placeholder' => $musician->getAttributeLabel('first_name'),
                    ]); ?>
                    <?= $form->textField($musician, "[$index]last_name", [
                        'class' => 'form__input',
                        'placeholder' => $musician->getAttributeLabel('last_name'),
                    ]); ?>
                    <?php $this->widget('zii.widgets.jui.CJuiDatePicker', [
                        'model' => $musician,
                        'attribute' => "[$index]birthdate",
                        'language' => 'ru',
                        'htmlOptions' => [
                            'class' => 'form__input',
                            'style' => 'width: 120px;',
                            'placeholder' => $musician->getAttributeLabel('birthdate'),
                        ],
                        'options' => [
                            'changeYear' => true,
                            'changeMonth' => true,
                            'dateFormat' => 'dd.mm.yy',
                            'defaultDate' => '-18y',
                            'minDate' => '01.01.' . (date('Y')-70),
                            'maxDate' => '31.12.' . (date('Y')-7),
                            'yearRange' => (date('Y')-70).':'.(date('Y')-7),
                        ],
                    ]); ?>
                    <?= $form->textField($musician, "[$index]instrument", [
                        'class' => 'form__input',
                        'style' => 'width: 20%;',
                        'placeholder' => $musician->getAttributeLabel('instrument'),
                    ]); ?>
                </div>

                <div class="form__row__small form__row--stretch">
                    <?= $form->textField($musician, "[$index]school", [
                        'class' => 'form__input',
                        'placeholder' => $musician->getAttributeLabel('school')
                    ]); ?>
                    <?= $form->textField($musician, "[$index]teacher", [
                        'class' => 'form__input',
                        'placeholder' => $musician->getAttributeLabel('teacher'),
                    ]); ?>
                    <?= $form->textField($musician, "[$index]class", [
                        'class' => 'form__input',
                        'style' => 'width: 20%;',
                        'placeholder' => $musician->getAttributeLabel('class'),
                    ]); ?>
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
            <div class="form__row form__row--stretch">
                <?= $form->textField($composition, "[$index]author", [
                    'class' => 'form__input',
                    'placeholder' => $composition->getAttributeLabel('author'),
                ]); ?>
                <?= $form->textField($composition, "[$index]title", [
                    'class' => 'form__input',
                    'placeholder' => $composition->getAttributeLabel('title'),
                ]); ?>
                <?php $this->widget('CMaskedTextField', [
                    'model' => $composition,
                    'attribute' => "[$index]duration",
                    'htmlOptions' => [
                        'class' => 'form__input',
                        'style' => 'width: 15%;',
                        'placeholder' => $composition->getAttributeLabel('duration'),
                    ],
                    'mask' => '9',
                ]); ?>
            </div>
            <?= $form->error($composition, "[$index]duration"); ?>
            <?php
        }
        ?>
        <?= $form->error($model, 'compositions'); ?>
    </div>
</div>

<div class="form__row">
    <?= $form->labelEx($model->request, 'demos'); ?>
    <?= $form->textArea($model->request, 'demos', [
        'class' => 'form__input',
        'placeholder' => 'Вкажіть в цьому полі посилання на демо-записи, а також будь-яку додаткову інформацію'
    ]); ?>
    <?= $form->error($model->request, 'demos'); ?>
    <p class="note">
        Ви можете безкоштовно завантажити свої записи на <a href="http://yotube.com">youtube.com</a>
        або <a href="http://ex.ua">ex.ua</a>. Для нас не важливо який сервіс ви використаєте, головне, щоб ваш демо-запис був достипним для перегляду.<br>
        Для завантаження на youtube, можете скористатися <?= \CHtml::link('інструкцією', ['/page/view', 'id' => 'how-to-youtube'], ['target' => '_blank']) ?>.
    </p>
    <p class="note">
        Якщо Ви граєте у супроводі концертмейстру або живого ансамблю, вкажіть тут інформацію про них, щоб наші ведучі могли вірно оголосити Ваш номер.
    </p>
</div>

<div class="form__controls">
    <?= \CHtml::hiddenField(\CHtml::modelName($model) . '[submitted]', 1) ?>
    <?= \CHtml::submitButton('Відправити', ['class' => 'button']) ?>
</div>

<?php $this->endWidget('CActiveForm'); ?>
</div>
