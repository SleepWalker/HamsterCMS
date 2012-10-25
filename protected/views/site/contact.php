<?php
$this->pageTitle=Yii::app()->name . ' - Contact Us';
$this->breadcrumbs=array(
	'Обратная связь',
);

$this->pageTitle = 'Обратная связь';
?>

<div class="contact_form">

<?php if(Yii::app()->user->hasFlash('contact')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('contact'); ?>
</div>

<?php else: ?>

<p>
Ели у вас есть вопросы по поводу работы нашей стуии или вы хотите воспользоваться нашими услугами, пожалуйста заполните следующую форму, что бы связаться с нами. Спасибо.
</p>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'contact-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Поля с пометкой <span class="required">*</span> являются обязательными.</p>

	<?php echo $form->errorSummary($model); ?>

<div class="contact_details">
  <div class="row activeLabel">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name'); ?>
	</div>
  <div class="row activeLabel">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email'); ?>
	</div>
</div>
  <div class="row activeLabel">
		<?php echo $form->labelEx($model,'subject'); ?>
		<?php echo $form->textField($model,'subject',array('size'=>60,'maxlength'=>128)); ?>
	</div>
  <div class="row activeLabel">
		<?php echo $form->labelEx($model,'body'); ?>
		<?php echo $form->textArea($model,'body',array('rows'=>6, 'cols'=>50)); ?>
	</div>
  
  <div class="row buttons">
		<?php echo CHtml::submitButton('Отправить'); ?>
	</div>
  
  <?php if(CCaptcha::checkRequirements()): ?>
	<div class="row">
		<div>
		<?php $this->widget('CCaptcha'); ?>
		<?php echo $form->textField($model,'verifyCode'); ?>
		</div>
    <?php echo $form->error($model,'verifyCode'); ?>
		<div class="hint">Пожалуйста введите цифры с изображения выше.
		<br/>Буквы не чувствительны к регистру.</div>
	</div>
	<?php endif; ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
</div>

<script type="text/javascript">
  // Показывалка/скрывалка label
  $('.activeLabel').each(function() {
    var container = this;
    $(this).find('input, textarea').focus(function() {
      $(container).find('label').hide();
    }).blur(function() {
      if($(this).val() == '') {
        $(container).find('label').show();
      }
    });
  });
</script>
<?php endif; ?>