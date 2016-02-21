<?php

/**
 * This is the model class for table "order_address".
 *
 * The followings are the available columns in table 'order_address':
 * @property string $id
 * @property string $user_id
 * @property string $street
 * @property string $house
 * @property string $flat
 * @property string $telephone
 *
 * The followings are the available model relations:
 * @property Order[] $orders
 * @property AuthUser $user
 */

use user\models\User;

class OrderAddress extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return OrderAddress the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'order_address';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('telephone', 'required'),
            array('street, house', 'required', 'on' => 'delivery'),
            array('user_id', 'length', 'max' => 11),
            array('street', 'length', 'max' => 300),
            array('house, flat', 'length', 'max' => 10),
            array('telephone', 'length', 'max' => 20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, street, house, flat, telephone', 'safe', 'on' => 'search'),
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
            'orders' => array(self::HAS_MANY, 'Order', 'adress_id'),
            'user' => array(self::BELONGS_TO, User::class, 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'street' => 'Город, улица (для заказов по Киеву город можно не указывать)',
            'house' => 'Дом',
            'flat' => 'Квартира',
            'telephone' => 'Телефон (Пример: (097) 777-07-07)',
        );
    }

    /**
     *  @return string полный адрес включая город, улицу, дом, квартиру
     */
    public function getFullAddress()
    {
        if (!$this->street) {
            return '';
        }

        $str = $this->street;
        if ($this->house) {
            $str .= ', ' . $this->house;
        }

        if ($this->flat) {
            $str .= ', ' . $this->flat;
        }

        return CHtml::encode($str);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('street', $this->street, true);
        $criteria->compare('house', $this->house, true);
        $criteria->compare('flat', $this->flat, true);
        $criteria->compare('telephone', $this->telephone, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
