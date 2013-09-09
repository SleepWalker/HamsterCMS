<?php

/**
 * This is the model class for table "event".
 *
 * The followings are the available columns in table 'event':
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $where
 * @property string $start_date
 * @property string $end_date
 * @property double $longitude
 * @property double $latitude
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
Yii::import('application.vendors.*');
require_once('alphaID.inc.php');

class Event extends CActiveRecord
{
  // В этой переменной будет хранится id ивента
  public $eventId;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Event the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'event';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, desc, where, start_date, longitude, latitude', 'required'),
			array('longitude, latitude', 'numerical'),
      array('location, end_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, desc, where, start_date, end_date, longitude, latitude', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название мероприятия',
			'desc' => 'Описание мероприятия',
			'where' => 'Место проведения',
			'start_date' => 'Дата начала',
      'end_date' => 'Дата конца',
			'longitude' => 'Coord X',
			'latitude' => 'Coord Y',
      'location' => 'Как добраться',
		);
	}
  
  /**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'name' => 'text',
      'start_date' => 'datetime',
      'end_date' => 'datetime',
      'where' => 'yandexAutoComplete',
      'location' => 'textareaTiny',
			'desc' => 'textarea',
      'longitude' => 'hidden',
      'latitude' => 'hidden',
		);
	}
  
  /**
   *  Заполняет {@link $eventId} необходимым id
   */
  protected function afterFind()
  {
    parent::afterFind();
    $this->eventId=alphaID($this->id, false, 4);
  }
  
  /**
   * Исчет мероприятие по его коду
   * @param string $id код мероприятия
   */
  public function findByEventId($id)
  {
    return $this->findByPk(alphaID($id, true, 4));
  }

  /**
   * Возвращает дату в красивом формате
   * @param string $date строка с датой из базы данных
   */
  public function prettyDate($date)
  {
    return Yii::app()->dateFormatter->formatDateTime($date, 'medium', 'short');
  }

  function getPrettyStartDate()
  {
    return $this->prettyDate($this->start_date);
  }

  function getPrettyEndDate()
  {
    return $this->prettyDate($this->end_date);
  }

  /**
   * @return string Html код части карты с метом проведения мероприятия
   */
  public function getImg() {
    return CHtml::image($this->src, $this->name);
  }

  /**
   * @return string ссылку на изображение куска карты яндекса
   */
  public function getSrc() {
    $params = array(
      'l' => 'map',
      'll' => $this->latitude . ',' . $this->longitude,
      'size' => '200,200',
      'z' => '15', // масштаб
      'pt' => $this->latitude . ',' . $this->longitude . ',flag', // метка
      'lang' => Yii::app()->sourceLanguage,
      //'key' => $this->module->params['yandexApiKey'],
    );
    return 'http://static-maps.yandex.ru/1.x/?'.http_build_query($params);
  }
  
  /**
   * @return string Html код кнопки "добавить в гуглокалендарь"
   */
  public function getGCalUrl()
  {
    // конвертируем временные зоны в +00:00
    date_default_timezone_set('Europe/Kiev');
    $utc0 = new DateTimeZone('Etc/GMT');
    $datetime = new DateTime($this->start_date);
    $datetime->setTimezone($utc0);
    $this->start_date = $datetime->format('Y-m-d H:i:s');
    
    $datetime = new DateTime($this->end_date);
    $datetime->setTimezone($utc0);
    $this->end_date = $datetime->format('Y-m-d H:i:s');
    
    $dates = date('Ymd\THis\Z',strtotime($this->start_date)) . '/' . date('Ymd\THis\Z',strtotime($this->end_date));
    $params = array(
      'action' => 'TEMPLATE',
      'text' => $this->name,
      'dates' => $dates,//dates=20121018T150000Z/20051231T230000Z
      'details' => $this->desc,
      'location' => $this->location,
      'trp' => false,
      //'sprop' => $event->viewUrl,
      'sprop' => 'name:'.Yii::app()->name,
    );
    return 'http://www.google.com/calendar/event?'.http_build_query($params);
  }
  
  /**
   * Возвращает url страницы материала
   */
	public function getViewUrl()
  {
    return Yii::app()->createUrl('event/event/view', array($this->eventId));
  }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('where',$this->where,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('longitude',$this->longitude);
		$criteria->compare('latitude',$this->latitude);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
