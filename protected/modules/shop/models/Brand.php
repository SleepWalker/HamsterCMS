<?php

/**
 * This is the model class for table "shop_brand".
 *
 * The followings are the available columns in table 'shop_brand':
 * @property integer $brand_id
 * @property string $brand_name
 * @property string $brand_alias
 * @property string $brand_logo
 *
 * The followings are the available model relations:
 * @property Shop[] $shops
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.models.Brand
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Brand extends CActiveRecord
{
  public static $uploadsUrl = '/uploads/shop/brand/';
  public $uImage;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ShopBrand the static model class
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
		return 'shop_brand';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('brand_name, brand_alias', 'required'),
			array('brand_name, brand_alias, brand_logo', 'length', 'max'=>128),
			array('uImage', 'file',
        'types'=>'jpg, gif, png',
        'maxSize'=>1024 * 1024 * 5, // 5 MB
        'allowEmpty'=>'true',
        'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
        'safe' => true,
			),
			array('brand_alias', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('brand_id, brand_name, brand_alias, brand_logo', 'safe', 'on'=>'search'),
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
			'shops' => array(self::HAS_MANY, 'Shop', 'brand_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'brand_id' => 'Brand',
			'brand_name' => 'Бренд',
			'brand_alias' => 'ЧПУ бренда',
			'brand_logo' => 'Лого',
			'uImage' => 'Лого',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'brand_name' => 'text',
			'brand_alias' => 'translit',
			'uImage' => 'file',
		);
	}
	
	/**
	 * Для надежности транслитерируем поле brand_alias
	 */
	function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      $this->brand_alias = Translit::url($this->brand_alias);
      return true;
    }
    else
      return false;
	}
	
	/**
   * Возвращает url страницы материала
   */
	public function getViewUrl()
  {
    return Yii::app()->createUrl('shop/brand/' . $this->brand_alias);
  }
  
  /**
   * Возвращает uri загрузки файлов
   */
  public function getUploadsUrl()
  {
    return self::$uploadsUrl;
  }
  
  /**
	 * @return array для использования в dropDownList
	 */
	public function getBrandsList()
	{
	  $list = $this->findAll(array('select'=>'brand_id, brand_name', 'order' => 'brand_name'));
	  foreach($list as $brandModel)
	  {
	    $newList[$brandModel->brand_id] = $brandModel->brand_name;
	  }
	  
	  return $newList;
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

		$criteria->compare('brand_id',$this->brand_id);
		$criteria->compare('brand_name',$this->brand_name,true);
		$criteria->compare('brand_alias',$this->brand_alias,true);
		$criteria->compare('brand_logo',$this->brand_logo,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
