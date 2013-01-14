<p>Здравствуйте, пользователь вашего сайта <b>"<?php echo Yii::app()->params['shortName'] ?>"</b> хочет, что бы вы с ним связались.</p>
<p>ФИО: <b><?php echo CHtml::encode($name) ?></b></p>
<?php 
if(!empty($phone))
  echo '<p>Телефон: <b>' . CHtml::encode($phone) .'</b></p>';
if(!empty($email))
  echo '<p>Email: <b>' . CHtml::encode($phone) .'</b></p>';
if(!empty($question))
  echo '<p>Вопрос:<br />' . CHtml::encode($question) .'</p>';

if($data)
{
?>
<br />
<p>Пользователя интересует следующий товар: 
<?php
  echo CHtml::link("{$data->product_name} ($data->id)", Yii::app()->createAbsoluteUrl(Yii::app()->createUrl('shop/shop/view', array('id'=>$data->page_alias))));
}
?></p>

<br />
С уважением, 
Hamster
