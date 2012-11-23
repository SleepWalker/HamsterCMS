<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName=>array('index'),
	$model->name,
);

$cs = Yii::app()->clientScript;

// Yandex Maps
Yii::app()->clientScript->registerScriptFile('http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=' . Yii::app()->sourceLanguage, CClientScript::POS_HEAD);// . '&

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
<?php
$this->widget('application.modules.sociality.widgets.HLike', array(
  'imgSrc' => $model->src,
  'description' => $model->desc,
  'title' => $model->name,
));

$this->widget('application.modules.sociality.widgets.HComment', array(
  'model' => $model,
));
?>
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
