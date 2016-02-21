<?php
/**
 * @var User $model
 * @var SiteController $this
 * @var $code код статуса операции смены пароля
 *    0 - ошибка восстановления пароля (не правильный хэш)
 *    1 - необходимо запросить емейл пользователя
 *    2 - такого емейла не существует
 *    3 - сообщение о том, что емейл был отправлен
 *    4 - запрос нового пароля
 *    5 - успешная смена пароля
 */
if(Yii::app()->user->isGuest)
{
  $this->pageTitle=Yii::app()->name . ' - Восстановление пароля';
  $this->breadcrumbs=array(
    'Восстановление пароля',
  );

  if($code == 1 || $code == 4)
  {
    $form=$this->beginWidget('CActiveForm', array(
      'id'=>'pass-form',
      'enableClientValidation'=>false,
    ));
    echo '<div class="form">';
    echo '<h1>Восстановление пароля</h1>';
  }

  switch($code)
  {
  case 1:
    echo '<p>Для восстановления пароля введите свой Email. На этот Email прийдет письмо с ссылкой на восстановление пароля.</p>';
    echo '<div class="row">' .
      $form->labelEx($model,'email') .
      $form->textField($model,'email') .
      $form->error($model,'email') .
      '</div>';
    break;
  case 2:
?>
           <h1>Ошибка</h1>
           <p>Такого Email не существует. Вы можете попробовать <a href="<?php echo Yii::app()->createUrl('site/chpass'); ?>">еще раз</a></p>
           <p>Для продолжения работы с сайтом вы можете вернуться на <a href="/">главную страницу</a>.</p>
<?php
    break;
  case 3:
?>
           <h1>Восстановление пароля</h1>
           <p>На указанный вами Email было отправленно письмо с ссылкой для восстановления пароля.</p>
           <p>Для продолжения работы с сайтом вы можете вернуться на <a href="/">главную страницу</a>.</p>
<?php
    break;
  case 4:
    echo '<p>Введите новый пароль для вашего аккаунта</p>'; 
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

    echo $form->hiddenField($model, 'email');
    break;
  case 5:
?>
           <h1>Смена пароля прошла успешно</h1>
           <p>Для продолжения работы с сайтом вернитесь на <a href="/">главную страницу</a>.</p>
           <p>Или можете воспользоваться формой входа:</p>
           <p><a href="<?php echo Yii::app()->createUrl('site/login'); ?>">Войти на сайт</a></p>
<?php
    break;

  default:
    throw new CHttpException(404,'Ошибка восстановления пароля');
  }

  if($code == 1 || $code == 4)
  {
?>
<div class="row buttons">
  <?php echo CHtml::submitButton('Отправить'); ?>
 </div>
<?php $this->endWidget(); ?>
</div><!-- form -->
<?php
  }
}
?>
