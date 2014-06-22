<?php
/**
 * YandexAutoComplete widget
 *
 * This widget extends CJuiAutoComplete and provides autocomplete from yandex's APIs.
 * When you provide latitudeAtt and longitudeAtt model fields, 
 * then they will be filled with longitude and latitude from Yandex's response
 *
 * Exaple:
 *    $controller->widget('application.widgets.yandex.YandexAutoComplete', array(
 *      'model'     => $model,
 *      'attribute' => 'address',
 *      'latitudeAtt' => 'latitude', // latitude model's field
 *      'longitudeAtt' => 'longitude', // longitude model's field
 *      // additional javascript options for the autocomplete plugin
 *      'options'=>array(
 *        'minLength'=>'2',
 *      ),
 *    ));
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.AdminModule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('zii.widgets.jui.CJuiAutoComplete');
class YandexAutoComplete extends CJuiAutoComplete {
  // model fields for latitude and longitude
  public $latitudeAtt;
  public $longitudeAtt;
  
  public function run() {
    $this->source = new CJavaScriptExpression('function( request, response ) {
        $.ajax({
            url: "http://geocode-maps.yandex.ru/1.x/?callback=?",
            dataType: "jsonp",
            data: {
                format: "json",
                results: 10,
                lang: "' . Yii::app()->sourceLanguage . '",
                geocode: request.term
            },
            success: function( data ) {
                response( $.map( data.response.GeoObjectCollection.featureMember, function( item ) {
                    var pos = item.GeoObject.Point.pos.split(" ");
                    return {
                        label: item.GeoObject.metaDataProperty.GeocoderMetaData.text,
                        value: item.GeoObject.metaDataProperty.GeocoderMetaData.text,
                        latitude: parseFloat(pos[0]),
                        longitude: parseFloat(pos[1]),
                    }
                }));
            }
        });
    }');
    
    // filling latitude und longitude fields with information from yandex maps
    if(isset($this->latitudeAtt) && isset($this->longitudeAtt))
    {
      // Yandex Maps
      Yii::app()->clientScript->registerScriptFile('http://api-maps.yandex.ru/2.0-stable/?load=package.standard&onload=yandexCallback&lang=' . Yii::app()->sourceLanguage, CClientScript::POS_HEAD);
      $latitudeName = CHtml::activeName($this->model,$this->latitudeAtt);
      $longitudeName = CHtml::activeName($this->model,$this->longitudeAtt);
      $latitudeId = CHtml::getIdByName($latitudeName);
      $longitudeId = CHtml::getIdByName($longitudeName);
      list($name,$id)=$this->resolveNameID();
      $this->options['select'] = new CJavaScriptExpression('function( event, ui ) {
          var latitude = ui.item.latitude;
          var longitude = ui.item.longitude;
          $("#' . $latitudeId . '").val(latitude);
          $("#' . $longitudeId . '").val(longitude);
          // showing map
          initMap(longitude, latitude);
      }');
      
      $initMap = 'window.initMap = function(longitude, latitude)
      {
        // showing map
        var ymaps = window.ymaps;
        var map = window.map;
        if(!$("#mapContainer").length) {
          var input = $("#' . $id . '");
          input.after(
            $("<div>").prop("id", "mapContainer")
            .height(400).width(400)
            .css({margin:"10px 0", color:"#d9d9d9"})
          );
          map = new ymaps.Map("mapContainer", {
            center: [longitude, latitude],
            zoom: 1,
            // enable zooming with sroller
            behaviors: ["default", "scrollZoom"],
          });
          // adding extra controls
          map.controls.add("zoomControl")
          .add("typeSelector");
          window.map = map;
        }
        // refreshing map
        map.setCenter([longitude, latitude]);
        // max zooming
        map.setZoom(23, {checkZoomRange: true});
        // marking place
        var placemark = new ymaps.Placemark(map.getCenter(), {hintContent: "<span style=\"color:#393939\">Подвинь меня!</span>"}, {preset: "twirl#whiteIcon", draggable: true});
        
        // refreshing coords values
        placemark.events.add("dragend", function (e) {
            var placemark = e.get("target");
            var pos = placemark.geometry.getCoordinates().toString().split(",");
            $("#' . $latitudeId . '").val(pos[1]);
            $("#' . $longitudeId . '").val(pos[0]);
        }, placemark.geometry);
        map.geoObjects.add(placemark);
      }';
      // Инициализация
      $initMap .= "\nwindow.yandexCallback = function() {";
      if(!empty($this->model[$this->latitudeAtt]) && !empty($this->model[$this->longitudeAtt]))
        $initMap .= "initMap({$this->model[$this->longitudeAtt]}, {$this->model[$this->latitudeAtt]});";
      $initMap .= "}";
      Yii::app()->getClientScript()->registerScript(__CLASS__ . 'initMap', $initMap); 
    }
    parent::run();
  }
}