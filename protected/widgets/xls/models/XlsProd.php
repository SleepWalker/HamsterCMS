<?php

/**
 * This is the model class for table "xls_prod".
 *
 * The followings are the available columns in table 'xls_prod':
 * @property string $id
 * @property string $brand_id
 * @property string $cat_id
 * @property string $name
 * @property string $dealer_price
 * @property string $sale_price
 *
 * The followings are the available model relations:
 * @property XlsBrand $brand
 * @property XlsCategorie $cat
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class XlsProd extends CActiveRecord
{
  // переменные для максимальной и минимальной цен
  public $max;
  public $min;
  protected $_minmax;
  
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Xls the static model class
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
		return 'xls_prod';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
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
			array('id, brand_id, cat_id, name, dealer_price, sale_price, min, max', 'safe', 'on'=>'search'),
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
			'brand' => array(self::BELONGS_TO, 'XlsBrand', 'brand_id'),
			'cat' => array(self::BELONGS_TO, 'XlsCategorie', 'cat_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Код',
			'brand_id' => 'Производитель',
			'cat_id' => 'Категория',
			'name' => 'Наименование товара',
			'dealer_price' => 'Dealer Price',
			'sale_price' => 'Цена, грн.',
      'min' => 'От',
      'max' => 'До',
		);
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
      $criteria->select = 'MIN(CAST( sale_price AS DECIMAL )) AS `min`, MAX(CAST( sale_price AS DECIMAL )) AS `max`';
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
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('brand_id',$this->brand_id);
		$criteria->compare('cat_id',$this->cat_id);
		$criteria->compare('name',$this->name,true);
		//$criteria->compare('dealer_price',$this->dealer_price,true);
		//$criteria->compare('sale_price',$this->sale_price);
    if($this->min || $this->max)
      $criteria->addBetweenCondition('CAST( sale_price AS DECIMAL )', $this->min, $this->max);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
      'pagination'=>array(
        'pageSize'=>25,
      ),
		));
	}
}