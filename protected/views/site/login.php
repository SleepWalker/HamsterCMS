<?php
if(Yii::app()->user->isGuest)
{
$this->pageTitle=Yii::app()->name . ' - Вход';
$this->breadcrumbs=array(
	'Вход',
);

echo '<div class="form loginForm' . (($_GET['ajax'])?' ajaxLoginForm':' normalLoginForm') . '">';
if(!$_GET['ajax']) // для ajax формы входа нам не надо выводить заголовок
  echo '<h1>Вход на сайт</h1>';
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'action' => Yii::app()->createUrl('site/login'),
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>


	<div class="row">
		<?php echo $form->labelEx($model,'user_email');?>
		<?php echo $form->textField($model,'user_email'); ?>
		<?php echo $form->error($model,'user_email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'user_password');?>
		<?php echo $form->passwordField($model,'user_password'); ?>
		<?php echo $form->error($model,'user_password'); ?>
	</div>

	<div class="row buttons">
		<?php 
		if($_GET['ajax'])
    {
      // Отключаем jquery
      Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 
      
		  echo CHtml::ajaxSubmitButton('Вход', '/site/login?ajax=1', array(
          'type' => 'POST',
          'context' => 'js:$(this)',
          'success' => 'js:function(data) {
            var dialog = $(this).parents(".ui-dialog-content");
            if(data == "ok")
              dialog.dialog("close");
            else
              dialog.html(data);
          }',
          'error' =>'js:function(a){console.log(a.responseText)}',
      ),
      array(
          'type' => 'submit',
          'live' => false,
          'id' => 'ajaxLogin',
      ));
    }
		else
		{
		  echo CHtml::submitButton('Вход');
//echo ' ' . CHtml::button('Восстановить пароль', array('onclick'=>"location.href ='" . Yii::app()->createUrl('site/chpass') . "'"));
		} 
		?>
	</div>
	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
		<p><?php echo CHtml::link('Забыли пароль?', Yii::app()->createUrl('site/chpass')); ?></p>
	</div>

<?php 
echo CHtml::hiddenField('backUrl', $_SERVER['HTTP_REFERER']);

$this->endWidget(); ?>
</div><!-- form -->

<?php
}
?>

<?php $this->widget('ext.hoauth.HOAuthWidget'); ?>
