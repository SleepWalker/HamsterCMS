<?php
namespace hamster\widgets;

/**
 * Address widget
 *
 * This widget provides address autocomplete from google's APIs.
 * When you provide latitudeAtt and longitudeAtt model fields,
 * then they will be filled with longitude and latitude
 *
 * Exaple:
 *    $controller->widget('hamster\\widgets\\PlacesAutoComplete', [
 *      'model'     => $model,
 *      'attribute' => 'address',
 *      'latitudeAtt' => 'latitude', // latitude model's field
 *      'longitudeAtt' => 'longitude', // longitude model's field
 *    ]);
 */
class PlacesAutoComplete extends \CWidget
{
    // model fields for latitude and longitude
    public $latitudeAtt;
    public $longitudeAtt;
    public $htmlOptions;
    public $model;
    public $attribute;
    public $name;
    public $value;

    public function run()
    {
        list($name, $id)=$this->resolveNameID();

        $googleApiKey = urlencode(\Yii::app()->params['googleApiKey'] ?? null);

        if (!$googleApiKey) {
            throw new \CException('Google maps API key is required');
        }

        if (isset($this->htmlOptions['id'])) {
            $id=$this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id']=$id;
        }
        if (isset($this->htmlOptions['name'])) {
            $name=$this->htmlOptions['name'];
        }

        // filling latitude und longitude fields with information from yandex maps
        if (isset($this->latitudeAtt) && isset($this->longitudeAtt)) {
            \Yii::app()->clientScript->registerScriptFile(
                "https://maps.googleapis.com/maps/api/js?key=$googleApiKey&libraries=places&callback=initMap",
                \CClientScript::POS_END,
                [
                    'async' => true,
                    'defer' => true,
                ]
            );
            $latitudeName = \CHtml::activeName($this->model, $this->latitudeAtt);
            $longitudeName = \CHtml::activeName($this->model, $this->longitudeAtt);
            $latitudeId = \CHtml::getIdByName($latitudeName);
            $longitudeId = \CHtml::getIdByName($longitudeName);
            // $this->options['select'] = new CJavaScriptExpression('function( event, ui ) {
            //     var latitude = ui.item.latitude;
            //     var longitude = ui.item.longitude;
            //     $("#' . $latitudeId . '").val(latitude);
            //     $("#' . $longitudeId . '").val(longitude);
            //     // showing map
            //     initMap(longitude, latitude);
            // }');

            $initMap = "(function() {
                var map;
                var autocomplete;
                var marker;
                var input;
                var zoom = 17;
                window.showOnMap = function(geometry) {
                    var location = geometry.location;

                    if (!location.lng || !location.lat) {
                        return;
                    }

                    if(!map) {
                        $(input).after(
                          $('<div>')
                              .prop('id', 'map')
                              .height(410)
                              .width(410)
                              .css({margin: '10px 0', color: '#d9d9d9'})
                        );

                        map = new google.maps.Map(document.getElementById('map'), {
                            center: location,
                            zoom: zoom
                        });
                        autocomplete.bindTo('bounds', map);

                        marker = new google.maps.Marker({
                            position: location,
                            map: map,
                        });
                    }

                    if (geometry.viewport) {
                        map.fitBounds(geometry.viewport);
                    } else {
                        map.setCenter(geometry.location);
                        map.setZoom(zoom);
                    }
                    marker.setPosition(location);
                };
                window.initMap = function initMap() {
                    input = document.getElementById('$id');

                    if (!input) {
                        throw new Error('Can not find input for address autocomplete');
                    }

                    autocomplete = new google.maps.places.Autocomplete(input);

                    autocomplete.addListener('place_changed', function() {
                        var place = autocomplete.getPlace();

                        place.geometry && showOnMap(place.geometry);
                    });

                    showOnMap({
                        location: {
                            lat: {$this->model[$this->latitudeAtt]},
                            lng: {$this->model[$this->longitudeAtt]}
                        }
                    });
                };
            }());";
            \Yii::app()->getClientScript()->registerScript(__CLASS__ . 'initMap', $initMap);
        }

        if ($this->hasModel()) {
            echo \CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo \CHtml::textField($name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Resolves name and ID of the input. Source property of the name and/or source property of the attribute
     * could be customized by specifying first and/or second parameter accordingly.
     * @param string $nameProperty class property name which holds element name to be used. This parameter
     * is available since 1.1.14.
     * @param string $attributeProperty class property name which holds model attribute name to be used. This
     * parameter is available since 1.1.14.
     * @return array name and ID of the input: array('name','id').
     * @throws CException in case model and attribute property or name property cannot be resolved.
     */
    protected function resolveNameID($nameProperty='name', $attributeProperty='attribute'): array
    {
        if ($this->$nameProperty!==null) {
            $name=$this->$nameProperty;
        } elseif (isset($this->htmlOptions[$nameProperty])) {
            $name=$this->htmlOptions[$nameProperty];
        } elseif ($this->hasModel()) {
            $name=\CHtml::activeName($this->model, $this->$attributeProperty);
        } else {
            throw new \CException(\Yii::t(
                'zii',
                '{class} must specify "model" and "{attribute}" or "{name}" property values.',
                array('{class}'=>get_class($this),'{attribute}'=>$attributeProperty,'{name}'=>$nameProperty)
            ));
        }

        if (($id=$this->getId(false))===null) {
            if (isset($this->htmlOptions['id'])) {
                $id=$this->htmlOptions['id'];
            } else {
                $id=\CHtml::getIdByName($name);
            }
        }

        return array($name,$id);
    }

    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel(): bool
    {
        return $this->model instanceof \CModel && $this->attribute!==null;
    }
}
