<?php
/**
 * @property boolean $accepted если true, значит админ подтвердил трансфер юзера в выбранную им роль
 * @property User $user модель пользователя, трансфер которого производился
 * @property string $chosenRole роль, которую выбрал пользователь
 */
?>
<p>Здравствуйте, <?php echo $user->fullName; ?></p><br/>

<?php
if($accepted)
{
?>
<p>Ваш запрос на группу "<b><?php echo $chosenRole ?></b>" на сайте <b>"<?php echo CHtml::link(Yii::app()->params['shortName'], Yii::app()->createAbsoluteUrl('/')) ?>"</b> был одобрен.
Теперь Вы можете выполнять все доступные вашей группе действия.</p>

<br>
Хорошего и успешного дня!
<?php
}else{
?>
<p>Ваш запрос на группу "<b><?php echo $chosenRole ?></b>" на сайте <b>"<?php echo CHtml::link(Yii::app()->params['shortName'], Yii::app()->createAbsoluteUrl('/')) ?>"</b>, к сожалению, был отклонен.</p>

<br>
Но все же, хорошего и успешного Вам дня!
<?php
}
?>