<?php

/**
 * This is the model class for table "price".
 *
 * The followings are the available columns in table 'price':
 * @property string $code
 * @property string $name
 * @property string $price
 * + неопределенное количество категорий
 *
 * The followings are the available model relations:
 * Все связи исключительно с таблицами категорий, которых может быть неопределенное количество
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.mevalScripts.inc.price.models.Price
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Price extends CActiveRecord
{
  //TODO: в модели есть несколько циклов по конфигу прайсов,
  // их можно обьединить, что бы сделать модель более производительной

  // переменные для максимальной и минимальной цен
  public $max;
  public $min;
  protected $_minmax;

  /**
   * @property array $catNames массив в котором хранятся названия категорий 
   *   (используется вместо стандартного механизма relation AR Yii в методе {@link __get()}
   */
  public static $catNames;
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Xls the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className)->with('cat');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'price';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
  {
    $config = HPrice::getConfig();
    $searchRule = '';
    foreach($config as $price)
    {
      if(is_array($price['columns']['cat']))
      {
        $searchRule .= ',' . implode('_id,', array_keys($price['columns']['cat'])) . '_id';
      }
    }
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, brand_id, cat_id, name, dealer_price, sale_price', 'required', 'on' => 'add'),
			array('id', 'length', 'max'=>32),
			array('brand_id, cat_id', 'length', 'max'=>10),
      array('name', 'length', 'max'=>256),
			array('dealer_price, sale_price', 'length', 'max'=>19),
      array('id', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('code, name, file_id, min, max'.$searchRule, 'safe', 'on'=>'search'),
		);
	}

	/**
   * из-за того, что единственные таблицы, 
   * с которыми должна быть связь - это категории, 
   * а их у нас может быть произвольное количество, из-за чего мне пришлось сделать 
   * общую модель на все категории, я решил не пытаться допиливать связи, так как это 
   * совсем не тривиальная задача (если это вообще возможно без переопределений классов).
   *
	 * @return array relational rules.
	 */
	public function relations()
	{
    return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
    $config = HPrice::getConfig();
    $labels = array();
    foreach($config as $price)
    {
      if(is_array($price['columns']['cat']))
      {
        foreach(array_keys($price['columns']['cat']) as $attId)
          $labels[$attId.'_id']  = $labels[$attId.'Name']  = isset($config['attributeLabels'][$attId]) 
          ? $config['attributeLabels'][$attId] : 'Категория';
      }
    }

		return array_merge(array(
			'code' => 'Код',
			'cat_id' => 'Категория',
			'name' => 'Наименование товара',
			'price' => 'Цена, грн.',
      'min' => 'От',
      'max' => 'До',
      'catName' => 'Категория',
      'file_id' => 'Название прайса',
		), $labels);
	}

  /**
   * Десерализируем дополнительные параметры модели
   */
  public function afterFind()
  {
    $this->extra = unserialize($this->extra);
  }

  public function primaryKey()
  {
    return $this->getMetaData()->tableSchema->primaryKey;
  }
  
  /**
   *  Возвращает максимальное/минимальное значение характеристики в зависимости от $minmax
   *  $minmax может принимать значения min или max
   */
  protected function range($minmax)
  {
    if(empty($this->_minmax))
    {
      $criteria = new CDbCriteria;
      $criteria->select = 'MIN(CAST( price AS DECIMAL )) AS `min`, MAX(CAST( price AS DECIMAL )) AS `max`';
      $this->_minmax = $this->find($criteria);
    }
    return (float)$this->_minmax->$minmax;
  }
  
  public function getMinValue()
  {
    return $this->range('min');
  }
  
  public function getMaxValue()
  {
    return $this->range('max');
  }

  /**
   * В метод добавлены изменения, которые позволяют получать имя 
   * категории (их у нас может быть больше одной) текущей модели, 
   * обращаясь к свойству $this->{$tableId . 'Name'} 
   *
   * По сути это аналог функции, приведенной ниже, одна за исключением того, 
   * что мы сможем получать имена из нескольких таблиц + в этом метод происходит 
   * эмуляция стандартного механизма relation AR Yii (так как мы не можем его 
   * использовать изз-за того, что у нас одна модель на все таблиц, которых может 
   * быть любое количество):
   *
   *   public function getCatName()
   *   {
   *     return $this->cat->name;
   *   }
   * 
   * @param string $name 
   * @access public
   * @return mixed
   * @see {@link PriceCat::findAll()}
   */
  public function __get($name)
  {
    if(preg_match('/^(\w+)Name$/', $name, $matches))
    {
      $tableId = $matches[1];
      if(!isset(self::$catNames[$tableId]))
      {
        // в методе {@link PriceCat::findAll()} сразу после выборки 
        // будет заполнен необходимый нам массив {@link Price::$catNames}
        $models = PriceCat::model($tableId)->findAll();
      }

      return self::$catNames[$tableId][$this->{$tableId . '_id'}];
    }
    return parent::__get($name);
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

		$criteria->compare('code',$this->code);
    // фильтр для категорий
    $config = HPrice::getConfig();
    foreach($config as $price)
    {
      if(is_array($price['columns']['cat']))
      {
        foreach(array_keys($price['columns']['cat']) as $attId)
          $criteria->compare($attId.'_id',$this->{$attId.'_id'});
      }
    }
		$criteria->compare('cat_id',$this->cat_id);
		$criteria->compare('file_id',$this->file_id);
		$criteria->compare('name',$this->name,true);
		//$criteria->compare('dealer_price',$this->dealer_price,true);
		//$criteria->compare('sale_price',$this->sale_price);
    if($this->min || $this->max)
      $criteria->addBetweenCondition('CAST( price AS DECIMAL )', $this->min, $this->max);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
      'pagination'=>array(
        'pageSize'=>25,
      ),
		));
	}
}
