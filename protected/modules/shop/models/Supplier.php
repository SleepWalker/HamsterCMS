<?php

/**
 * This is the model class for table "shop_supplier".
 *
 * The followings are the available columns in table 'shop_supplier':
 * @property integer $id
 * @property string $name
 */
class Supplier extends CActiveRecord
{
  public $code; // тоже что и id, только с маской 00
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Supplier the static model class
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
		return 'shop_supplier';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, code', 'safe', 'on'=>'search'),
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
			'name' => 'Имя',
			'code' => 'Код'
		);
	}
	
  /**
  * @return array типы полей для форм администрирования модуля
  */
	public function getFieldTypes()
	{
		return array(
			'name' => 'text',
		);
	}
	
	/**
	 * @return array для использования в dropDownList
	 */
	public function getSuppliersList()
	{
	  $list = $this->findAll(array('select'=>'id, name', 'order' => 'name'));
	  foreach($list as $model)
	  {
	    $newList[$model->id] = $model->name;
	  }
	  
	  return $newList;
	}
	
	/**
	 *  Преабразовываем id в строку длиной в два символа (если id<10, то пустые места заполняются нулями)
	 */
	protected function getCode() 
	{
	  if($this->id)
	    return str_pad($this->id, 2, "0", STR_PAD_LEFT);
	}
	
	/**
	 *  Присваиваем переменной $code значение id с масской 00
	 */
	protected function afterFind() 
	{
	  if($this->id)
	    $this->code = str_pad($this->id, 2, "0", STR_PAD_LEFT);
	}
	
	/**
   * Возвращает url страницы материала
   */
	public function getViewUrl()
  {
    //возвращаем false, так как у нас нету вьюх для этой модели
    return false;
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

		$criteria->compare('id',$this->code,true);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
        'attributes'=>array(
          'code'=>array(
            'asc'=>'id',
            'desc'=>'id DESC',
          ),
          '*',
        ),
      ),
		));
	}
}