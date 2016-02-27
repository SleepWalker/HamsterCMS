<?php
if (Yii::app()->user->isGuest) {
    $this->pageTitle=Yii::app()->name . ' - Вход';
    $this->breadcrumbs= ['Вход'];

    echo '<div class="form loginForm' . (isset($_GET['ajax'])?' ajaxLoginForm':' normalLoginForm') . '">';
    if (!isset($_GET['ajax'])) { // для ajax формы входа нам не надо выводить заголовок
        echo '<h1>Вход на сайт</h1>';
    }
?>

<?php $form=$this->beginWidget('CActiveForm', [
    'id' => 'login-form',
    'action' => Yii::app()->user->loginUrl,
    'enableClientValidation' => true,
    'clientOptions' => [
        'validateOnSubmit' => true,
    ],
]); ?>


    <div class="row">
        <?= $form->labelEx($model, 'user_email');?>
        <?= $form->textField($model, 'user_email'); ?>
        <?= $form->error($model, 'user_email'); ?>
    </div>

    <div class="row">
        <?= $form->labelEx($model, 'user_password');?>
        <?= $form->passwordField($model, 'user_password'); ?>
        <?= $form->error($model, 'user_password'); ?>
    </div>

    <div class="row buttons">
        <?php
        if (isset($_GET['ajax'])) {
            // Отключаем jquery
            Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;

            echo CHtml::ajaxSubmitButton('Вход', '/user/login?ajax=1', [
                'type' => 'POST',
                'context' => 'js:$(this)',
                'success' => 'js:function(data) {
                    var dialog = $(this).parents(".ui-dialog-content");
                    if(data == "ok") {
                        dialog.dialog("close");
                    } else {
                        dialog.html(data);
                    }
                }',
                'error' =>'js:function(a){console.log(a.responseText)}',
            ], [
                'type' => 'submit',
                'live' => false,
                'id' => 'ajaxLogin',
            ]);
        } else {
            echo CHtml::submitButton('Вход');
//echo ' ' . CHtml::button('Восстановить пароль', array('onclick'=>"location.href ='" . Yii::app()->createUrl('user/chpass') . "'"));
        }
        ?>
    </div>
    <div class="row rememberMe">
        <?= $form->checkBox($model, 'rememberMe'); ?>
        <?= $form->label($model, 'rememberMe'); ?>
        <?= $form->error($model, 'rememberMe'); ?>
        <p><?= CHtml::link('Забыли пароль?', Yii::app()->createUrl('user/chpass')); ?></p>
    </div>

<?php $this->endWidget('CActiveForm'); ?>
</div><!-- form -->

<?php
}
?>

<?php
    $this->widget('sleepwalker\hoauth\widgets\HOAuth');
?>
