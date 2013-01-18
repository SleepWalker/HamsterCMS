<?php
/**
 * View file for displaying shop product
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
 $this->breadcrumbs=array_merge (
  $model->cat->breadcrumbs,
	array(
	  $model->brand->brand_name=>Yii::app()->createUrl('shop/brand/'.$model->brand->brand_alias),
	  $model->product_name
	)
);

// кэш товара будет обновлятся только после редактирования товара
if(YII_DEBUG || $this->beginCache($model->page_alias, array(
  'dependency'=>array(
    'class'=>'system.caching.dependencies.CDbCacheDependency',
    'sql'=>'SELECT MAX(edit_date) FROM shop',
  )
))) { 

$this->pageTitle = $model->page_title;

// подключаем цсс, необходимый для отображения вьюхи
if(empty($this->module->params['viewCssFile']))
  $this->module->registerCssFile('view.css');
else
  $this->module->registerCssFile($this->module->params['viewCssFile'], true);
?>
<article>
<div style="float:right;margin-top:10px;">(Код товара: <b><?php echo $model->code; ?></b>)</div>
<h1><?php echo $model->product_name; ?></h1>
<div class="productLeft">
<?php
$this->beginWidget('application.widgets.lightbox.HLightBox', array(
  'id'=>'prod_photo',
));

$width = 280;
$src = array_shift($model->photo);
echo CHtml::link(
  CHtml::image(Shop::imgSrc($src, $width), $this->pageTitle, array('width'=>$width)),
  Shop::imgSrc($src)
);
$width = 45;
echo '<div class="smallPreviews">';
foreach($model->photo as $src) {
  echo CHtml::link(
    CHtml::image(Shop::imgSrc($src, $width), $this->pageTitle, array('width'=>$width)),
    Shop::imgSrc($src)
  );
}
echo '</div>';
 
$this->endWidget('application.widgets.lightbox.HLightBox');

$this->widget('application.modules.sociality.widgets.HLike', array(
  'imgSrc' => Shop::imgSrc($model->photo[0], 120),
  'description' => $model->description,
  'title' => $model->product_name,
));
?>
<p>
<?php 
$this->widget('application.widgets.EStarRating',array(
  'model' => $model,
  'attribute' => 'ratingVal', // mark 1...5
  'callbackUrl' => Yii::app()->createUrl('shop/shop/rating'),
	'readOnly'=>Yii::app()->user->isGuest,
));?>
</p>

</div>

<div style="float:right">
<div class="avaibility"><?php
echo $model->statusName;
?></div>

<section class="pricePart">
<b><?php
if($model->price)
  echo number_format($model->price, 2, ',', ' ') . ' грн.'; 
else
  echo 'Цену уточняйте';

?></b>
  <?php 
  if(is_array(Yii::app()->params['currency']) && Yii::app()->params->currency['toDollar'])
    echo number_format(round($model->price/Yii::app()->params->currency['toDollar']), 2, ',', ' ') . ' $'; 
  ?>
</section>
<div class="additionalActions">
  <?php
  if($model->status == Shop::STATUS_AVAIBLE)
    echo '<a href="/cart/add/'. $model->id . '" id="buyButton"><input type="button" value="Купить / В корзину" /></a>';
  else
    echo '<a href="" onclick="return false" style="opacity:0.5"><input type="button" value="Купить / В корзину" /></a>';
  ?>
  <a href="/shop/compare/add/<?php echo $model->id ?>?thumb=<?php echo $model->photo[0] ?>&cat=<?php echo $model->cat->cat_alias ?>" id="compareButton"><input type="button" value="Сравнить" /></a>
</div>
<p style="clear:both;padding-top:5px;">
<?php 
  if(is_array(Yii::app()->params['currency']) && Yii::app()->params->currency['toDollar'] && Yii::app()->params->currency['toEmoney'])
    echo 'Б/Н и электронные деньги: <b>' . number_format(round($model->price/Yii::app()->params->currency['toDollar']*Yii::app()->params->currency['toEmoney']), 2, ',', ' ') . ' грн.</b>';
?>
<?php if ($model->waranty != '') { ?>
<span style="float:right;">Гарантия <b><?php echo $model->waranty ?></b> мес.</span>
<?php } ?>
</p>
</div>

<?php if (preg_match ('/.*\w.*/', $model->description)) { ?>
<div class="desc">
  <h2>Описание <?php echo $model->product_name ?></h2>
    <div id="descriptionSnippet">
      <?php echo $model->description; ?>
    </div>
    <?php 
    Yii::app()->getClientScript()
      ->registerScript('showDetails','
      var $snippet = $("#descriptionSnippet");
      if($snippet.height() + 20 < $snippet[0].scrollHeight)
      {
        $snippet.addClass("shortView");
        $("<a href=\"\">Смотреть полностью</a>")
        .bind("click", function() {
          $(this).remove();
          $snippet.removeClass("shortView");
          return false;
        })
        .prop("id", "showDetails")
        .insertAfter($snippet);
      }', CClientScript::POS_END);
    ?>
  </div>
<?php } ?>
<br class="clear" />

<?php
  $chars = Char::model()->findAll(array(
      'condition'=>'t.prod_id=' . $model->id . ' AND char_value<>""',
      //'order'=>'charShema.char_name',
  ));
  
  $charShemas = CharShema::model()->findAllByCat($model->cat_id);
  // перестраиваем в $char[$id] = charItem;
  foreach($chars as $char)
  {
    $newChars[$char->char_id] = $char;
  }
  $chars = $newChars;
?>
<section class="tabs">
  <menu>
    <?php
    if(count($chars))
      echo '<a href="#characteristics">Полные характеристики</a>';
    ?>
    <a href="#photo">Фото</a>
    <a href="#comments">Отзывы</a>
    <a href="/page/payment_and_delivery#delivery">Доставка</a>
    <!--a href="#characteristics">Обзоры</a-->
  </menu>
  
  <?php    
    if(count($chars))
    {
      function echoHeaderInTable($name)
      {
        echo '</table><h3>' . $name . '</h3><table class="items">';
      }
    ?>
    <section id="characteristics">
      <table class="items">
        <tbody>
          <?php
            if ($model->waranty)
              echo '<tr><td>Гарантия, <b>мес.</b></td><td>' . $model->waranty . '</td></tr>';
              
            foreach($charShemas as $charShema)
            {
              if($charShema->isHidden) continue;
              $char = $chars[$charShema->char_id];
              $suffix = $charShema->hasSuffix ? ", <b>".$charShema->char_suff . "</b>": "";
              
              // добавляем в массив со строками текущую характеристику, если у нее есть значение
              if(!empty($char))
              {
                ob_start();
                echo '<tr><td>' . $charShema->char_name . $suffix . '</td>
                <td>' . $char->value . '</td></tr>';
                $charRow[$char->char_id] = ob_get_clean();
              }elseif($charShema->isCaption && $charShema->type == 1) // Отображаем характеристику как заголовок
              {
                ob_start();
                echoHeaderInTable($charShema->char_name);
                $charRow[$charShema->char_id] = ob_get_clean();
              }
              
              // для характеристик в режиме заголовка, у которых есть варианты выбора
              // мы создадим отдельный массив в котором будет соответствие заголовка характеристикам
              if($charShema->isCaption && !$charShema->hasSuffix)
              {
                $charCaptionData[$char->char_id] = $charShema->ddMenuArr['relatedArr'];
                $i = 0;
                // Пересчитываем $charCaptionData в $captionName=>$relatedChars
                foreach($charShema->ddMenuArr['items'] as $captionName)
                {
                  $charCaptionData[$char->char_id][$captionName] = $charCaptionData[$char->char_id][$i];
                  unset($charCaptionData[$char->char_id][$i++]);
                }
              }
            }
            
             
            // обьединяем заголовки с зависимыми от них характеристиками в одно целое
            foreach($charRow as $charId => $char)
            {
              if(isset($charCaptionData[$charId]))
              {
                ob_start();
                foreach($charCaptionData[$charId] as $caption => $relatedChars)
                {
                  echoHeaderInTable($caption);
                  foreach($relatedChars as $relatedCharId)
                  {
                    echo $charRow[$relatedCharId];
                    unset($charRow[$relatedCharId]); // Подчищаем общий массив
                  }
                }
                $charRow[$charId] = ob_get_clean();
              }
            }
            
            // печатаем таблицу
            echo implode('', $charRow);
          ?>
        </tbody>
      </table>
    
    </section> <!-- #characteristics -->
    <?php
    }
  ?>
  
  <section id="photo">
  <?php
  $this->beginWidget('application.widgets.lightbox.HLightBox', array(
    'id'=>'prod_photo_tab',
  ));

    foreach($model->photo as $index => $src) {
      echo CHtml::link(
      $model->img(120, $index),
      Shop::imgSrc($src)
      );
    }
   
  $this->endWidget('application.widgets.lightbox.HLightBox');
  ?>
  </section>
  <section id="delivery">
    <h2>Доставка и отставка</h2>
  </section>
  <section id="comments">
    <?php
    $this->widget('application.modules.sociality.widgets.HComment', array(
      'model' => $model,
    ));
    ?>
  </section><!-- #comments -->
</section> <!-- / .tabs -->
</article>

<?php
$js = <<<EOF
$('section.tabs > menu a').eq(0).addClass('active');
$('section.tabs > section').eq(0).show();
$('.tabs > menu a').on('click', function() {
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
  $('.tabs > section').hide();


  // Определяем, что мы покажем
  var id = this.hash;
  if(this.pathname != location.pathname) // Ajax Подгрузка
  {
    // TODO: возможность полного кэширования вкладки (тоесть без отправки запроса на серв)
    // Сохраняем изначальное содержимое контейнера
    if(!$(id).data('initial')) $(id).data('initial', $(id).html());
    // сразу вставляем эту инфу в элемент (таким образом при повторном открытии таба его контент будет ресетится на "дефолтный") 
    $(id).html($(id).data('initial'));
    $.ajax({
      url: '/api' + this.pathname,
      success: function(data) {
        $(id).append(data.content);
      },
      complete: function() {
        $(id).show(); 
      },
      error: function() {
        $(id).append('<em class="tabsNoData">Нет данных</em>');
      },
      dataType: 'json',
    });
  }
  else
    $(id).show();  
  
  return false;
});

$("#compareButton").on("click", function() {
  $.ajax({
    url:$(this).prop("href"),
    context:$(this),
    success:function() {
      activateCompare();
    },
  });
  return false;
});

function activateCompare()
{
  $("#compareButton").replaceWith('<div class="compareAjaxMessage">Добавлен к <a href="/shop/compare/{$model->cat->cat_alias}">сравнению</a></div>');
}
EOF;

// Если товар уже добавлен в сравнение, переключаем кнопку на "Товар добавлен к сравнению"
$compare = Yii::app()->session['ProdCompare'];  
if($compare && isset($compare[ $model->cat->cat_alias ][ $model->id ]))
  $js .= "\n" . 'activateCompare()';


Yii::app()->getClientScript()->registerScript(__CLASS__.'#Tabs', $js);

$this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
  'selectors' => array(
    '#buyButton',
  ),
  'themeUrl' => '/css/jui',
  'options' => array(
    'title' => 'Товар успешно добавлен в ваш список покупок!',
  ),
));

if(!YII_DEBUG) // в дебаге не кешируем
  $this->endCache();
}
