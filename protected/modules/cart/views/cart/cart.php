<?php
/**
 * View file for cart
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.views.cart
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
$this->pageTitle = 'Ваша корзина';

$this->breadcrumbs[] = $this->pageTitle;

foreach($models as $model) 
  $total += $model->quantity;

// сразу кидаем юзера на последний шаг
// скрипт валидации шагов сам определится на какой именно шаг его перекинуть
$action = '/cart/order?step=last';

?>
<div id="cartContent" class="form">
<?php
echo '<div id="cartCancelOrder">' . CHtml::ajaxButton('Отменить заказ', $this->createUrl('/cart/clear'), array('complete'=>'js: function() {location.href="/"}')) . '</div>';
?>
<h1>В вашей корзине <span class='qtotal'><?php echo $total ?></span> <? echo $this->pluralForm($total, 'товар', 'товара', 'товаров')  ?></h1>
<?php 
if($total)
{
  echo CHtml::form($action);
?>
<table>
<?php 
foreach($models as $index => $model) 
{
  // $index == $model->id за исключением случая с вариантами, 
  // в котором к id еще добавляется хэш варианта
  $check += $model->price * $model->quantity;
  $price[$index] = $model->price;
  $quantity[$index] = $model->quantity;
  ?>
  <tr>
  <td><a href="<?php echo $model->viewUrl ?>" target="_blank"><?php echo $model->img ?></a></td>
  <td><a href="<?php echo $model->viewUrl ?>" target="_blank"><b><?php echo $model->product_name ?></b></a>
  <?php
  if(isset($model->variants))
  {
    echo '<dl>';
    foreach($model->variants as $name => $value)
      echo "<dt>$name</dt><dd>$value</dd>";
    echo '</dl>';
  }
  ?>
</td>
  <td style="min-width:85px;">
  <?php echo number_format($model->price, 2, ',', ' ') ?> грн.
  <?php 
  // сума по товарам текущей позиции
  if($model->quantity > 1) echo '<div class="prodSumm">' . number_format($model->price * $model->quantity, 2, ',', ' ') . ' грн.</div>' 
  ?>
  </td>
  <td><input type="number" class="quantity" id="q<?php echo $index ?>" name="quantity[<?php echo $index ?>]" value="<?php echo $model->quantity; ?>" min="1" max="500" size="4"></td>
  <td><a href="<?php echo Yii::app()->createUrl('cart/cart/remove', array('id' => $index)) ?>" id="d<?php echo $index ?>" class="delLink">Удалить</a></td>
  </tr>
  <?php  
}
?>
<tr><td><b>Итого:</b></td><td></td><td id="summary"><?php echo number_format($check, 2, ',', ' '); ?> грн.</td>
<td colspan="2" align="center">
<?php
if(!$hidePayButton)
  echo CHtml::ajaxSubmitButton('Перейти к оплате', $action.'&ajax=1', array( // добавляем в GET запрос переменную ajax=1 (сработает только если у юзера включен JS)
      'type' => 'POST',
      'success' => 'replaceContent',
      'beforeSend' => 'startLoad',
      'complete' => 'stopLoad',
  ),
  array(
      'type' => 'submit',
      'live' => false,
      'style'=> 'font-size:14px',
  ));
?>
</td>
</tr>
</table>
<?php 
echo CHtml::endForm();
?>
</div>

<script type="text/javascript">
var quantity = <?php echo CJSON::encode($quantity) ?>;
var price =  <?php echo CJSON::encode($price) ?>;
</script>
<?php
// подключаем jui
Yii::import('application.widgets.juiajaxdialog.AjaxDialogWidget');
$jui = new AjaxDialogWidget;
$jui->themeUrl = '/css/jui'; 
$jui->options['title'] = 'Корзина пуста';
$jui->initScripts();

  Yii::app()->getClientScript()->registerScript('orderJs', $js);
} // конец if
