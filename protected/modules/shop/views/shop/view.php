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
if($this->beginCache($model->page_alias, array(
  'dependency'=>array(
    'class'=>'system.caching.dependencies.CDbCacheDependency',
    'sql'=>'SELECT MAX(edit_date) FROM shop',
  )
))) { 

$this->pageTitle = $model->page_title;


$cs = Yii::app()->clientScript;

$socialScript = "
(function(d, s, id) {
  var div = d.createElement('div');
  div.id = 'fb-root';
  d.body.insertBefore(div, d.body.firstChild);
  var js, fjs = d.getElementsByTagName(s)[0]; 
  if (d.getElementById(id)) {return;} 
  js = d.createElement(s); js.id = id; 
  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1' 
  fjs.parentNode.insertBefore(js, fjs); 
 }(document, 'script', 'facebook-jssdk'));
 
 (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
  
  window.___gcfg = {
    lang: 'ru',
    parsetags: 'onload'
  };
  
  !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');
  
  VK.init({apiId: " . Yii::app()->params['vkApiId'] . ", onlyWidgets: true});
  
  VK.Widgets.Like('vk_like', {type: 'vertical', height: 24}); 
  VK.Widgets.Comments('vkcomments', {limit: 10, width: '730', attach: '*'});
";
 
$cs->registerScriptFile('http://userapi.com/js/api/openapi.js?43', CClientScript::POS_END);
//$cs->registerScriptFile('//platform.twitter.com/widgets.js');
//$cs->registerScriptFile('https://apis.google.com/js/plusone.js',CClientScript::POS_HEAD);
//$cs->registerScript(__CLASS__ . '#VkScript', 'VK.init({apiId: 3071168, onlyWidgets: true});', CClientScript::POS_HEAD);
$cs->registerScript(__CLASS__ . '#SocialScript', $socialScript, CClientScript::POS_END);

$desc = preg_replace('/\n/', '', strip_tags(mb_substr($model->description, 0, 200, 'UTF-8')));
$cs->registerMetaTag($desc, 'description');
$cs->registerMetaTag($desc, NULL, NULL, array('property' => 'og:description'));
$cs->registerMetaTag($model->product_name, NULL, NULL, array('property' => 'og:title'));
$cs->registerMetaTag('product', NULL, NULL, array('property' => 'og:type'));
//$cs->registerMetaTag('Ссылка на материал', NULL, NULL, array('property' => 'og:url'));
$cs->registerMetaTag(Yii::app()->name, NULL, NULL, array('property' => 'og:site_name'));
$imgSrc = Yii::app()->createAbsoluteUrl(Shop::imgSrc($model->photo[0], 120));
$cs->registerMetaTag($imgSrc, NULL, NULL, array('property' => 'og:image'));
$cs->registerLinkTag('image_src', NULL, $imgSrc);


// подключаем цсс, необходимый для отображения вьюхи
if(empty($this->module->params['viewCssFile']))
  $this->module->registerCssFile('view.css');
else
  $this->module->registerCssFile($this->module->params['viewCssFile'], true);
?>
<article>
<div style="float:right;margin-top:10px;">(Код товара: <b><?php echo $model->id; ?></b>)</div>
<h1><?php echo $model->product_name; ?></h1>
<div class="productLeft">
<?php
$this->beginWidget('application.widgets.lightbox.HLightBox', array(
  'id'=>'prod_photo',
));

  $width = 280;
  foreach($model->photo as $src) {
    echo CHtml::link(
    CHtml::image(Shop::imgSrc($src, $width), $this->pageTitle, array('width'=>$width)),
    Shop::imgSrc($src)
    );
    $width = 45;
  }
 
$this->endWidget('application.widgets.lightbox.HLightBox');
?>
<div class="soc_buttons"> 
  <div class="sbutton"><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="">Tweet</a></div> 
  <div class="sbutton" style="padding-top:1px;"><fb:like send="false" style="width:49px;overflow:hidden;" layout="box_count" show_faces="true"></fb:like></div> 
  <div class="sbutton" style="padding-top:2px;"><g:plusone size="tall"></g:plusone></div> 
  <div class="sbutton" style="padding-top:7px;"><div id="vk_like"></div></div> 
</div>

<p style="clear:both;">
<?php 
$this->widget('CStarRating',array(
  'name'=>'rating',
  'callback'=>'
      function(){
        url = "/shop/rating";
        jQuery.get(url, {id: "'.$model->id.'", val: $(this).val()}, function(data) {
          var tip = $("#rating_success");
          tip.html(data.answer);           
          if(data.status == "success") 
            tip[0].data = parseInt(tip[0].data)+1;  

          var totalVotes = tip[0].data;
          window.pauseTips = setTimeout(function() {tip.html(totalVotes);window.pauseTips=false},3000);
        }, "json");}',
  'focus'=>'function(value, link){
    if (window.pauseTips) return;
    var tip = $("#rating_success");
    tip[0].data = tip[0].data || tip.html();
    tip.html(link.title || "value: "+value);
  }',
  'blur'=>'function(value, link){
    if (window.pauseTips) return;
    var tip = $("#rating_success");
    tip.html(tip[0].data || "");
  }',
  'minRating' => '1',
	'maxRating' => '5',
	'ratingStepSize' => '1',
	'value' => $model->ratingVal, // mark 1...5
	'allowEmpty'=>false,
	'titles'=>array(1=>'Ужасно', 'Плохо', 'Нормально', 'Хорошо', 'Отлично'),
	'readOnly'=>Yii::app()->user->isGuest,
	'cssFile'=>false,
));?>
<span id="rating_success" style="text-indent:5px; vertical-align: 3px;">(<?php echo $model->votesCount ?>)</span>
</p>

</div>

<div style="float:right">
<div class="avaibility"><?php
echo $model->statusName;
?></div>

<section class="pricePart">
  <b><?php echo number_format($model->price, 2, ',', ' '); ?> грн.</b>
  <?php echo number_format(round($model->price/Yii::app()->params->currency['toDollar']), 2, ',', ' '); ?> $
</section>
<div class="additionalActions">
  <?php
  if($model->status == Shop::STATUS_PUBLISHED)
    echo '<a href="/cart/add/'. $model->id . '" id="buyButton"><input type="button" value="Купить / В корзину" /></a>';
  else
    echo '<a href="" onclick="return false" style="opacity:0.5"><input type="button" value="Купить / В корзину" /></a>';
  ?>
  <a href="/shop/compare/add/<?php echo $model->id ?>?thumb=<?php echo $model->photo[0] ?>&cat=<?php echo $model->cat->cat_alias ?>" id="compareButton"><input type="button" value="Сравнить" /></a>
</div>
<p style="clear:both;padding-top:5px;">
<?php 
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
    <div id="descriptionSnippet" class="shortView">
      <?php echo $model->description; ?>
    </div>
    <?php echo CHtml::link('Смотреть полностью', '#', array('id'=>"showDetails"));
    Yii::app()->getClientScript()
    ->registerScript('showDetails','
      jQuery("#showDetails").bind("click", function() {
        $(this).css({display:"none"});
        $("#descriptionSnippet").removeClass("shortView");
        return false;
      });', CClientScript::POS_END);
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
    <a href="#vkcomments">Отзывы</a>
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
                <td>' . $char->char_value . '</td></tr>';
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
    
    </section>
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
  <!--section id="delivery">
    <h2>Доставка и отставка</h2>
  </section-->
  <section id="vkcomments"></section>
</section> <!-- / .tabs -->
</article>

<?php
$js = <<<EOF
$( $('.tabs menu a')[0] ).addClass('active');
$( $('section.tabs section')[0] ).show();
$('.tabs menu a').on('click', function() {
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
  $('.tabs section').hide();


  // Определяем, что мы покажем
  var id = this.hash;
  if(this.pathname != location.pathname) // Ajax Подгрузка
  {
    //T!: возможность полного кэширования вкладки (тоесть без отправки запроса на серв)
    // Сохраняем изначальное содержимое контейнера
    if(!$(id).data('initial')) $(id).data('initial', $(id).html());
    // сразу вставляем эту инфу в элемент (таким образом при повторном открытии таба его окнтент будет рсетится на "дефолтный") 
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

$this->endCache();}
