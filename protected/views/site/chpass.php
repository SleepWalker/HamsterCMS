<?php
if(Yii::app()->user->isGuest)
{
$this->pageTitle=Yii::app()->name . ' - Восстановление пароля';
$this->breadcrumbs=array(
	'Восстановление пароля',
);

echo '<div class="form">';
echo '<h1>Восстановление пароля</h1>';
if($model->isNewRecord)
{
  echo '<p>Для восстановления пароля введите свой Email. На этот Email прийдет письмо с ссылкой на восстановление пароля.</p>';
}else{
  echo '<p>Введите новый пароль для вашего аккаунта</p>'; 
}
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'pass-form',
	'enableClientValidation'=>false,
)); ?>

<?php
if($model->isNewRecord)
{
	echo '<div class="row">' .
  	$form->labelEx($model,'email') .
  	$form->textField($model,'email') .
  	$form->error($model,'email') .
  	'</div>';
}
else
{
  echo '<div class="row">' .
  	$form->labelEx($model,'password1') .
  	$form->passwordField($model,'password1') .
  	$form->error($model,'password1') .
  	'</div>';
	
	echo '<div class="row">' .
  	$form->labelEx($model,'password2') .
  	$form->passwordField($model,'password2') .
  	$form->error($model,'password2') .
  	'</div>';
  	
  echo CHtml::hiddenField('id', $model->id) .
    CHtml::hiddenField('h', $model->chpassHash);
}
?>

	<div class="row buttons">
		<?php 
		echo CHtml::submitButton('Отправить');
		?>
	</div>

<?php 
echo CHtml::hiddenField('backUrl', $_SERVER['HTTP_REFERER']);

$this->endWidget(); ?>
</div><!-- form -->

<?php
}
?>
