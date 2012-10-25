<?php
/**
 * View file for product compartion in module shop
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.views.compare
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
$this->breadcrumbs= array(
  $models[0]->cat->cat_name => $this->createUrl('/shop/categorie/'.$models[0]->cat->cat_alias),
  'Сравнение товаров',
);
$this->pageTitle = 'Сравнение товаров';
echo '<h1>'.$this->pageTitle.'</h1>';
?>
<a href="/shop/compare/reset?cat=<?php echo $models[0]->cat->cat_alias; ?>"><input type="button" value="Сброс сравнения" /></a>
<p id="charFilter"><a href="" class="active">Все характеристики</a> | <a href="">Отличия</a></p>
<table id="compareTable" width="100%">
<tr id="tableCaption">
<th>
</th>
<?php
// снипеты товаров (шапка таблицы)
foreach ($models as $model)
{
  ?>
  <th>
  <a href="/shop/compare/remove/<?php echo $model->id . '?cat=' . $model->cat->cat_alias; ?>&ajax=1" class="ui-corner-all" style="float:left;position:relative;" role="button"><span class="ui-icon ui-icon-trash">close</span></a>
  <a href="<?php echo $model->viewUrl ?>">
    <?php 
    echo $model->product_name; 
    ?>
  </a>
  <br /><div>Гарантия <b><?php echo $model->waranty ?></b> мес.</div>
  <a href="<?php echo $model->viewUrl ?>">
    <?php 
    echo $model->img(120); 
    ?>
  </a>
  <div>
  <?php $model->ratingWidget($this); ?>
  </div>
  <p style="font-weight:normal">
  <b style="color:#609d00;"> <?php echo number_format($model->price, 2, ',', ' ') ?> грн. </b><br />
  <?php echo number_format(round($model->price/Yii::app()->params->currency['toDollar']), 2, ',', ' ') ?> $
  </p>
  <a href="/cart/add/<?php echo $model->id ?>" class="button buyButton">Купить</a>
  </th>
  <?php
}
echo '</tr>';

// характеристики
foreach ($charCompareArr as $charName => $charValues)
{
  echo '<tr><td>'.$charName.'</td>';
  foreach($charValues as $charValue)
    echo '<td>'. (($charValue != '')?$charValue:'—').'</td>';
  echo '</tr>';
}
echo '</table>';

// ajax dialog для кнопки купить
$this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
  'selectors' => array(
    '.buyButton',
  ),
  'themeUrl' => '/css/jui',
  'options' => array(
    'title' => 'Корзина',
  ),
));
?>
<script type="text/javascript" >

// Отличия
$("#charFilter a").eq(1).click(function() {
  $(this).parent().nextAll('table').eq(0).find('tr').each(function (index) {
    if(index == 0) return true; // пропускаем шапку с сниппетами товаров
    var prevVal = false;
    var hide = true;
    $(this).find('td').each(function(index) {
      if(index == 0) return true; // пропускаем ячейку с названием характеристики
      if (prevVal)
        hide = ($(this).html() == prevVal) && hide;
      prevVal = $(this).html();
    });
    if(hide)
      $(this).hide();
  });
  
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
  return false;
});

// Показать все характеристики
$("#charFilter a").eq(0).click(function() {
  $(this).parent().nextAll('table').eq(0).find('tr').each(function () {
    $(this).show();
  });
  
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
  return false;
});

<?php 
if(count($models) > 1)
  // Показываем различия
  echo '$("#charFilter a").eq(1).click();';
?>

$(window).scroll(function() {
  if(!$('#tableCaption').data('capPos'))
    $('#tableCaption').data('capPos', getOffsetRect($('#tableCaption')[0]))
  var capPos = $('#tableCaption').data('capPos');
  var start = capPos.top;
  // Создаем таблицу и копируем в нее строку заголовка
  if(!$('#floatingCaption').length)
    $("<table>").css({
      position: 'fixed',
      top: 0,
      left: capPos.left,
      zIndex: 99,
    })
    .prop('id', 'floatingCaption')
    // Копируем в созданную таблицу строки из исходной
    .append(
      $('#tableCaption').clone(true, true)
    )
    // Копируем длину исходной таблицы
    .width($('#tableCaption').width())
    .each(function() {
      $(this).find('th').each(function(index) {
        // задаем каждой ячейке длину из исходной таблицы
        $(this).width( $('#tableCaption').children().eq(index).width() )
      });
    })
    .appendTo("body");
  if (document.documentElement.scrollTop > start || document.body.scrollTop > start)
  {
    $('#floatingCaption').css('display', 'table');
  }else
    $('#floatingCaption').css('display', 'none');
});

$("#tableCaption a.ui-corner-all")
.hover(
  function() {
  	$(this).addClass('ui-state-hover').css('margin','0 0 0 -2px');
  },
  function() {
  	$(this).removeClass("ui-state-hover").css('margin','0');
})
.click(function() {
  $.ajax({
    url: $(this).prop('href'),
    context: $(this).parents('th'),
    success: function() {
      // определяем индекс
      var indexToRemove = 1;
      var prev = $(this).prev();
      while(prev[0] != $(this).siblings()[0])
      {
        indexToRemove++;
        prev = prev.prev();
      }
      $('#compareTable').find('tr').each(function() {
        $(this).find('td, th').eq(indexToRemove).remove();
      });
      // обновляем плавающую таблицу
      $('#floatingCaption').remove();
      $(window).scroll();
    },
  });
  return false;
});

function getOffsetRect(elem) {
  var box = elem.getBoundingClientRect()

  var body = document.body
  var docElem = document.documentElement

  var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
  var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

  var clientTop = docElem.clientTop || body.clientTop || 0
  var clientLeft = docElem.clientLeft || body.clientLeft || 0

  var top  = box.top +  scrollTop - clientTop
  var left = box.left + scrollLeft - clientLeft

  return { top: Math.round(top), left: Math.round(left) }
}

</script>