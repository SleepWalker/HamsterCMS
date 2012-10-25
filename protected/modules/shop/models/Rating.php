<?php

/**
 * This is the model class for table "shop_rating".
 *
 * The followings are the available columns in table 'shop_rating':
 * @property string $id
 * @property string $prod_id
 * @property string $user_id
 * @property integer $value
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Shop $prod
 */
class Rating extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ShopRating the static model class
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
		return 'shop_rating';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('prod_id, user_id, value', 'required'),
			//array('prod_id, user_id', 'unique'),
			array('prod_id, user_id', 'numerical', 'integerOnly'=>true),
			array('value', 'in', 'range'=>array(1,2,3,4,5)),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, prod_id, user_id, value', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'prod' => array(self::BELONGS_TO, 'Shop', 'prod_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'prod_id' => 'Prod',
			'user_id' => 'User',
			'value' => 'Value',
		);
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
		$criteria->compare('prod_id',$this->prod_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('value',$this->value);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
