<?php
$this->pageTitle = 'HumsterCMS - Вход';
?>
<table class="loginPage"><tr><td>
<h1>Форма входа</h1>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'action' => Yii::app()->createUrl('admin/login/index'),
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>
    <div class="row">
    <?php echo $form->textField($model,'user_email'); ?>
    <?php echo $form->error($model,'user_email'); ?>
    </div>
    <div class="row">
		<?php echo $form->passwordField($model,'user_password'); ?>
    <?php echo $form->error($model,'user_password'); ?>
    </div>
      <p><input type="submit" value="Войти" /></p>
      <p>
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
    </p>
<?php $this->endWidget(); ?>
</div>
</td></tr></table>
