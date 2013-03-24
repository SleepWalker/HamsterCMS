<?php

/**
 * По умолчанию модель привязана к таблице price_cat, 
 * но по сути она предназначена для работы с несколькими таблицами категорий
 *
 * Доступные колонки
 * @property string $id
 * @property string $name
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.price.models.PriceCat
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PriceCat extends CActiveRecord
{
  /**
   * @property string $tableName таблица для которой инициализируется модель по умолчанию, 
   *    если при вызове метода {@link PriceCat::model()} не задать другую
   */
  private $_tableName = 'price_cat';

  /**
   * @property array $_models используется аналогичным образом как и такая же переменная в родительском классе
   */
  private static $_models=array();      // class name => model
	private $_md;								// meta data

  /**
   * Этот метод полностью соответствует аналогичному в CActiveRecord,
   * однако в нем добавлена возможность инициализировать одно и туже модель для разных таблиц
   * 
   * @param mixed $className 
   * @param mixed $tableName имя таблицы в базе данных (можно без префикса 'price_'), 
   *        для которой должен создаться экземпляр модели
   * @static
   * @access public
   * @return CActiveRecord
   */
  public static function model($tableName = false, $className=__CLASS__)
  {
    if(strpos($tableName, 'price_') !== 0)
      $tableName = 'price_' . $tableName;

    if($tableName === null) $className=null;
    if(!$tableName)
      return parent::model($className);

    if(isset(self::$_models[$tableName.$className]))
      return self::$_models[$tableName.$className];
    else
    {
      $model=self::$_models[$tableName.$className]=new $className(null);
      $model->_tableName = $tableName;
      $model->_md=new CActiveRecordMetaData($model);
      $model->attachBehaviors($model->behaviors());
      return $model;
    }
  }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return $this->_tableName;
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
      array('name', 'unique'),
      array('name', 'length', 'max'=>128),
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
  
  public function scopes()
  {
    return array(
      'default'=>array(
        'order'=>'name',
      ),
    );
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Cat Name',
		);
	}

  /**
   * Этот метод отличается от родительского тем, что после того, 
   * как была произведена первая выборка всех категорий из бд, 
   * этот метод добавляет элемент в статический массив {@link Price::$catNames}, 
   * который используется в методе {@link Price::__get()}.
   * Это уменьшает количество запросов в бд
   * 
   * @param string $condition 
   * @param array $params 
   * @access public
   * @return array
   * @see {@link Price::__get()}
   */
  public function findAll($condition='',$params=array())
  {
    $models = parent::findAll($condition,$params);

    // кешируем названия категорий
    if(!isset(Price::$catNames[$tableId]))
    {
      $tableId = str_replace('price_','', $this->tableName());
      foreach($models as $model)
        Price::$catNames[$tableId][$model->primaryKey] = $model->name;    
    }
    return $models;
  }

	/**
	 * Returns the meta-data for this AR
	 * @return CActiveRecordMetaData the meta for this AR class.
	 */
	public function getMetaData()
	{
		if($this->_md!==null)
			return $this->_md;
		else
			return $this->_md=self::model($this->tableName())->_md;
	}
}
