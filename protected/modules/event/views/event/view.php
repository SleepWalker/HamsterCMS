<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName=>array('index'),
	$model->name,
);

$cs = Yii::app()->clientScript;

// Yandex Maps
Yii::app()->clientScript->registerScriptFile('http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=' . Yii::app()->sourceLanguage, CClientScript::POS_HEAD);// . '&

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
  VK.Widgets.Comments('vkcomments', {limit: 10, attach: '*'});
";
$cs->registerScriptFile('http://userapi.com/js/api/openapi.js?43', CClientScript::POS_END);
$cs->registerScript(__CLASS__ . '#SocialScript', $socialScript, CClientScript::POS_END);

// SEO and Sociality meta
$desc = strip_tags(mb_substr(array_shift(explode('{%CUT%}', $model->desc)), 0, 200, 'UTF-8'));
$cs->registerMetaTag($desc, 'description');
$cs->registerMetaTag($desc, NULL, NULL, array('property' => 'og:description'));
$cs->registerMetaTag($model->name, NULL, NULL, array('property' => 'og:title'));
$cs->registerMetaTag('product', NULL, NULL, array('property' => 'og:type'));
//$cs->registerMetaTag('Ссылка на материал', NULL, NULL, array('property' => 'og:url'));
$cs->registerMetaTag(Yii::app()->name, NULL, NULL, array('property' => 'og:site_name'));
/*$imgSrc = Yii::app()->createAbsoluteUrl(Post::imgSrc($model->image));
$cs->registerMetaTag($imgSrc, NULL, NULL, array('property' => 'og:image'));
$cs->registerLinkTag('image_src', NULL, $imgSrc);*/

$this->pageTitle = $model->name;
?>

<article class="eventFullView">
  <header>
    <h1><?php echo $model->name ?></h1>
  </header>

	<section>
    <div id="mapContainer"></div>
    <?php echo $model->desc ?>
    <p><strong>Где</strong>: <?php echo CHtml::encode($model->where) ?></p>
    <p><strong>Начало</strong>: <?php echo $model->prettyStartDate ?></p>
    <?php if($model->end_date)
    {
      echo '<p><strong>Конец</strong>: ' . $model->prettyEndDate . '</p>';
    }?>
    <p><strong>Как добраться</strong>: <?php echo CHtml::encode($model->location) ?></p>
    <p><strong>Добавить в календарь</strong>: 
    <a href="<?php echo $model->gCalUrl ?>" class="icon icon_gcal" title="Добавить в Google Calendar">Добавить в Google Calendar</a>
    <a href="<?php echo Yii::app()->createUrl('event/event/ical', array('id' => $model->eventId));  ?>" class="icon icon_ical" title="iCalendar (*.ics)">iCalendar (*.ics)</a>
    </p>
	</section>
  <footer>
    <div class="soc_buttons"> 
      <div class="sbutton"><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="">Tweet</a></div> 
      <div class="sbutton" style="padding-top:1px;"><fb:like send="false" style="width:49px;overflow:hidden;" layout="box_count" show_faces="true"></fb:like></div> 
      <div class="sbutton" style="padding-top:2px;"><g:plusone size="tall"></g:plusone></div> 
      <div class="sbutton" style="padding-top:7px;"><div id="vk_like"></div></div> 
    </div>
    <section id="vkcomments" style="clear:both;"></section>
  </footer>
</article>

<?php
ob_start();
?>
var myMap;
var ymaps = window.ymaps;
var longitude = <?php echo (float)$model->longitude ?>;
var latitude = <?php echo (float)$model->latitude ?>;;
ymaps.ready(function () {
    map = new ymaps.Map("mapContainer", {
        center: [longitude, latitude],
        zoom: 1,
        // включаем масштабирование карты колесом
        behaviors: ['default', 'scrollZoom'],
    });
    map.setZoom(23, {checkZoomRange: true});
    map.controls.add('zoomControl')
    .add('typeSelector');
    
    <?php if(isset($model->how_to_get))
    {
    ?>
      map.balloon.open(map.getCenter(),
      {
          contentHeader: 'Как добраться?',
          contentBody: <?php echo CJavaScript::encode($model->how_to_get) ?>,
      });
    <?php
    }else{
    ?>
      var placemark = new ymaps.Placemark(map.getCenter(), {}, {preset: "twirl#greenIcon"});
      map.geoObjects.add(placemark);
    <?php
    }
    ?>
});
<?php
$cs->registerScript(__CLASS__ . 'maps', ob_get_clean());
?>
