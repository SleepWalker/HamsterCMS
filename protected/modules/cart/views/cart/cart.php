<?php
/**
 * View file for cart
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.views.cart
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
Yii::app()->getClientScript()->registerCoreScript('jquery');

$this->pageTitle = 'Ваша корзина';

$this->breadcrumbs[] = $this->pageTitle;

foreach($models as $model) 
  $total += $model->quantity;

// сразу кидаем юзера на последний шаг
// скрипт валидации шагов сам определится на какой именно шаг его перекинуть
$action = '/cart/order?step=last';

?>
<div id="catContent" class="form">
<?php
echo '<div style="float:right;margin-top:6px;">' . CHtml::ajaxButton('Отменить заказ', $this->createUrl('/cart/clear'), array('complete'=>'js: function() {location.href="/"}')) . '</div>';
?>
<h1>В вашей корзине <span class='qtotal'><?php echo $total ?></span> <? echo $this->pluralForm($total, 'товар', 'товара', 'товаров')  ?></h1>
<?php 
if($total)
{
  echo CHtml::form($action);
?>
<table>
<?php 
foreach($models as $model) 
{
  $check += $model->price * $model->quantity;
  $price[$model->id] = $model->price;
  $quantity[$model->id] = $model->quantity;
  ?>
  <tr>
  <td><a href="<?php echo $model->viewUrl ?>" target="_blank"><?php echo $model->img ?></a></td>
  <td><a href="<?php echo $model->viewUrl ?>" target="_blank"><b><?php echo $model->product_name ?></b></a></td>
  <td style="min-width:85px;">
  <?php echo number_format($model->price, 2, ',', ' ') ?> грн.
  <?php 
  // сума по товарам текущей позиции
  if($model->quantity > 1) echo '<div class="prodSumm">' . number_format($model->price * $model->quantity, 2, ',', ' ') . ' грн.</div>' 
  ?>
  </td>
  <td><input type="number" class="quantity" id="q<?php echo $model->id ?>" name="quantity[<?php echo $model->id ?>]" value="<?php echo $model->quantity; ?>" min="1" max="500" size="4"></td>
  <td><a href="/cart/clear/<?php echo $model->id ?>" id="d<?php echo $model->id ?>" class="delLink">Удалить</a></td>
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
$js = <<<EOD
/*
* Изменение количества товаров
*/
$('body').on('change click', '.quantity', function() {
  var id = $(this).prop('id').slice(1);
  var newQuantity = $(this).val()*1;
  quantity[id] = newQuantity;
  
  // Обрабатываем цену (сумма, что напротив товара)
  var priceTd = $(this).parents('td').prev();
  if(!priceTd.find('.prodSumm').length)
    priceTd.append('<div class="prodSumm">' + priceTd.html() + '</div>');
  priceTd = priceTd.find('.prodSumm');
  var newPrice = number_format(price[id] * quantity[id]);
  
  priceTd.html(newPrice + priceTd.html().replace(/[\d,]/g, ''));
  
  // Обновляем сумму и колличество товаров
  countSummary();
});

/*
* Удаление из корзины
*/
$('body').on('click', '.delLink', function() {
  var row = $(this).parents('tr');
  var id = $(this).prop('id').slice(1);
  $.ajax({
    url: $(this).prop('href'),
    success: function()
    {
      delete price[id];
      delete quantity[id];
      // Обновляем сумму
      countSummary();
      
      // сообщаем, что корзина пуста и переадресовываем юзера на главную
      if(row.parents('table').find('tr').length <= 2  && location.href.indexOf('/cart') != -1) // 2 потому, что одну строку мы сейчас удалим и еще одна строка с суммой
      { 
        $('#yt0').hide(); // прячем кнопку продолжения заказа
        runDialog('<br />Ваша корзина пуста, через <span id="redirectTimer">3</span> секунд вы будете перемещены на <a href="/">главную страницу</a>');
        setInterval(function() {
          var wait = $("#redirectTimer").html()*1;
          if(wait)
            $("#redirectTimer").html(--wait);
          else 
            location.href = "/";
        }, 1000);
      }     
      row.remove();
    } 
  }); 
  return false;
});

/**
* Обновляет сумму покупки
**/
function countSummary()
{
  var summ = 0;
  var amount = 0;
  for (i in price)
  {
    summ += price[i] * quantity[i];
    amount += quantity[i] * 1;
  }
    
  // устанавливаем сумму и колличество товаров (функция, предоставляемая виджетом cartStatus)
  cartSetValues(amount, summ);
  
  summ = number_format(summ);
  $('#summary').html(summ + $('#summary').html().replace(/[\d,]/g, ''));
}

/**
* Форматирует число как цену
**/
function number_format(number)
{
  number = number.toFixed(2);
  number = number.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ').replace(".", ",");
  return number;
}
EOD;

  Yii::app()->getClientScript()->registerScript('orderJs', $js);
} // конец if