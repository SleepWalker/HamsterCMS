<?php

/**
 * This is the model class for table "order".
 *
 * The followings are the available columns in table 'order':
 * @property string $id
 * @property string $user_id
 * @property string $address_id
 * @property integer $status
 * @property integer $type
 * @property string $ip
 * @property string $date
 * @property int $operator_id id of operator, that has manualy added this order
 * @property string $comment to order
 *
 * The followings are the available model relations:
 * @property OrderAddress $address
 * @property AuthUser $user
 * @property OrderCheck[] $orderChecks
 *
 * @package    shop.ShopController
 */

use user\models\User;

class Order extends CActiveRecord
{
    // Варианты оплаты заказа
    // @see getOrderCurrency()
    protected $_orderCurrency = array(
        1 => 'Оплата наличными',
        //2 => 'WMR',
        //3 => 'WMZ',
        4 => 'WMU',
        5 => 'Приват24: UAH',
        //6 => 'Приват24: USD',
        //7 => 'Приват24: EUR',
        8 => 'Безналичный расчет',
    );

    // Типы заказа
    // @see getOrderType()
    protected $_orderType = array(
        1 => 'Доставка курьером по Киеву',
        2 => 'Самовывоз',
        3 => 'Доставка службой "Нова пошта" по Украине',
    );

    const NOT_COMPLETE = 1;
    const COMPLETE = 2;
    const IN_PROCESS = 3;
    const CANCELED = 4;

    public $orderStatus = array(
        self::NOT_COMPLETE => 'Не выплнено',
        self::COMPLETE => 'В процессе',
        self::IN_PROCESS => 'Выполнено',
        self::CANCELED => 'Отмена',
    );

    // массив с id статусов по которым надо фильтровать с помощью search();
    public $statusArr;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Order the static model class
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
        return 'order';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('address_id, type, currency', 'required'),
            array('status, type, currency', 'numerical', 'integerOnly' => true),
            array('type, status, currency', 'default', 'value' => 1, 'setOnEmpty' => true),
            array('comment, user_id', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, address_id, status, type, ip, date, statusArr', 'safe', 'on' => 'search'),
        );
    }

    /**
     *  Сохраняем ip юзера
     *  Обновляем даты
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->date = new CDbExpression('NOW()');
                $this->ip = ip2long(CHttpRequest::getUserHostAddress());
            } else {
                $this->ip = ip2long($this->ip);
            }

            return true;
        } else {
            return false;
        }

    }

    /**
     *  Преобразуем ip в адекватный формат
     */
    protected function afterFind()
    {
        $this->ip = long2ip($this->ip);
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'address' => array(self::BELONGS_TO, 'OrderAddress', 'address_id'),
            'user' => array(self::BELONGS_TO, User::class, 'user_id'),
            'client' => array(self::BELONGS_TO, 'Client', array('id' => 'order_id')),
            'operator' => array(self::BELONGS_TO, User::class, array('operator_id' => 'id')),
            'check' => array(self::HAS_MANY, 'OrderCheck', 'order_id'),
        );
    }

    /**
     *  Возвращает ссылку на просмотр чека
     **/
    public function getViewUrl()
    {
        return '/admin/cart/check/' . $this->id;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'address_id' => 'address',
            'status' => 'Статус заказа',
            'currency' => 'Способ оплаты',
            'type' => 'Тип заказа',
            'ip' => 'Ip',
            'date' => 'Дата заказа',
            'operator_id' => 'Оператор',
            'comment' => 'Комментарий оператора',
        );
    }

    /**
     * Возвращает доступные валюты для использования в форме оформления заказа
     *
     * @access public
     * @return array
     */
    public function getOrderCurrency()
    {
        $currencyParams = isset(Yii::app()->modules['cart']['params']['emoney'])
        ? Yii::app()->modules['cart']['params']['emoney']
        : array();

        // прочие, не электронные, способы оплаты
        if (is_array($currencyParams['other'])) {
            foreach ($currencyParams['other'] as $currencyId) {
                $enabledCurrencies[$currencyId] = $this->_orderCurrency[$currencyId];
            }
            unset($currencyParams['other']);
        }

        foreach ($currencyParams as $emoneyId => $emoney) {
            if ($emoney['active'] == true) {
                switch ($emoneyId) {
                    case 'WM':
                        $enabledCurrencies[4] = $this->_orderCurrency[4];
                        break;
                    case 'Privat24':
                        $enabledCurrencies[5] = $this->_orderCurrency[5];
                        break;
                }
            }

        }

        if (!count($enabledCurrencies)) {
            throw new CException('Настройте хоть один способ оплаты в админкe');
        }

        return $enabledCurrencies;
    }

    /**
     * Возвращает доступные способы доставки
     *
     * @access public
     * @return array
     */
    public function getOrderType()
    {
        $typeParams = Yii::app()->modules['cart']['params']['deliveryTypes'];

        if (!count($typeParams)) {
            throw new CException('Настройте хоть один способ доставки');
        }

        foreach ($typeParams as $typeId) {
            $enabledTypes[$typeId] = $this->_orderType[$typeId];
        }

        return $enabledTypes;
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

        //$criteria->compare('id',$this->id,true);
        //$criteria->compare('user_id',$this->user_id,true);
        //$criteria->compare('address_id',$this->address_id,true);
        //$criteria->compare('status',$this->status);
        $criteria->addInCondition('status', $this->statusArr);
        //$criteria->compare('type',$this->type);
        //$criteria->compare('ip',$this->ip,true);
        //$criteria->compare('date',$this->date,true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => array('defaultOrder' => 'date DESC'),
        ));
    }
}
