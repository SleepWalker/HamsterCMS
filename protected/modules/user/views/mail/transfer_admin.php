<?php
/**
 * @property User $user модель пользователя, который только что зарегистрировался
 * @property string $chosenRole роль, которую выбрал пользователь
 */
?>
<p>Здравствуйте,<br/>на вашем сайте <b>"<?php echo Yii::app()->params['shortName'] ?>"</b> зарегистрировался новый пользователь, который хочет, что бы его переместили в группу "<b><?php echo $chosenRole ?></b>".</p>
<h3>Информация о пользователе:</h3>
<p>ФИО: <b><?php echo CHtml::encode($user->fullName) ?></b></p>
<p>Email: <b><?php echo CHtml::encode($user->email) ?></b></p>
<p>Дата регистрации: <b><?php echo CHtml::encode($user->date_joined) ?></b></p>

<br />
<p>Переместить пользователя в выбранную группу (или отказать ему) вы можете в админ панели HamsterCMS или кликнув на ссылку ниже:<br />
<?php
  $link = Yii::app()->createAbsoluteUrl(Yii::app()->createUrl('admin/admin/user')) . '/transfer/assign/'.$user->primaryKey;
  echo CHtml::link($link, $link);
?></p>
<p>Что бы отказать пользователю, воспользуйтесь ссылкой ниже:<br />
<?php
  $link = Yii::app()->createAbsoluteUrl(Yii::app()->createUrl('admin/admin/user')) . '/transfer/revoke/'.$user->primaryKey;
  echo CHtml::link($link, $link);
?></p>

<br />
С уважением, 
Hamster