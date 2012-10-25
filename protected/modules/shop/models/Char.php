<?php

/**
 * This is the model class for table "shop_char".
 *
 * The followings are the available columns in table 'shop_char':
 * @property string $prod_id
 * @property string $char_id
 * @property string $char_value
 *
 * The followings are the available model relations:
 * @property ShopCharShema $char
 * @property Shop $prod
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.models.Char
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Char extends CActiveRecord
{
  // Поля из таблицы shop_char_shema (так как эта модель все время работает в join с shop_char_shema)
  /*public $cat_id;
  public $char_name;
  public $char_suff;*/
  
  // минимальные и максимальные значения для фильтра
  public $min;
  public $max;
  protected $_minmax;
  
  // это поле типа характеристики из модели CharShema
  // используется для валидации полей характеристик в зависимости от их типа
  public $type;
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Char the static model class
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
		return 'shop_char';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('char_id', 'required'),
      //!T: после обновления Yii раскомментировать
      //array('prod_id, char_value', 'required', 'except' => 'validate'),
			array('prod_id', 'length', 'max'=>11),
			array('char_id', 'length', 'max'=>10),
			array('min, max, type', 'numerical'),
      array('char_value', 'charValidator', 'on'=>'validate'),
			array('char_value', 'length', 'max'=>300),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('char_id, char_value', 'safe', 'on'=>'search'),
		);
	}
  
  protected function beforeValidate()
  {
    if(parent::beforeSave())
    {
      // если $charValue - массив - сохраняем значение checkbox
      if(is_array($this->char_value)) $this->char_value = implode('; ', $this->char_value);
      return true;
    }
    else
      return false;
  }
  
  /**
   *  Делает обязательными все поля, кроме текстовых
   */
  public function charValidator($attribute, $params)
  {
    if(/*$this->type && $this->type != 1 && $this->type != 6 &&*/ $this->$attribute == '')
      $this->addError($this->char_id . '_' . $attribute, 'Это поле не может быть пустым');
    if($this->type == 4 && !is_numeric($this->$attribute))
      $this->addError($this->char_id . '_' . $attribute, 'Значение поля должно быть цифрой');
  }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'charShema' => array(self::BELONGS_TO, 'CharShema', 'char_id'),
			'prod' => array(self::BELONGS_TO, 'Shop', 'prod_id'),
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
      $criteria->select = 'MIN(CAST( char_value AS DECIMAL )) AS `min`, MAX(CAST( char_value AS DECIMAL )) AS `max`';
      $criteria->compare('char_id', $this->char_id);
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
   *  Используется, что бы задать id характеристики и далее применить методы getMinRange и getMaxRange используя chain стиль
   *  Cart::setId($id)->minRange;
   */
  static public function setId($charId) 
  {
    $model = new Char;
    $model->char_id = $charId;
    return $model;
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'prod_id' => 'Prod',
			'char_id' => 'Char',
			'char_value' => 'Значение характеристики',
			'min' => 'От',
			'max' => 'До',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	/*public function getFieldTypes()
	{
		return array(
			'char_value' => 'text',
			'charShema.char_name' => 'text',
			//'image' => 'file',
		);
	}*/

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('prod_id',$this->prod_id,true);
		$criteria->compare('char_id',$this->char_id,true);
		$criteria->compare('char_value',$this->char_value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
