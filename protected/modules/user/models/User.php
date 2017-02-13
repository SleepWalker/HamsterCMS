<?php
/**
 * This is the model class for table "auth_user".
 *
 * The followings are the available columns in table 'auth_user':
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $bio
 * @property string $birthdate
 * @property string $photo
 * @property integer $is_active
 * @property string $last_login
 * @property string $date_joined
 *
 * The followings are the available model relations:
 * @property ShopRating[] $shopRatings
 */

namespace user\models;

use user\models\LoginForm;
use \AuthItem;

class User extends \CActiveRecord
{
    public $meta;
    public $uploadedPhoto;

    /**
     * @property string $role роль выбранная юзером при регистрации
     */
    public $role;

    /**
     * @property array $rolesList писок ролей для выбора в форме с помощью radiolist
     */
    protected $_rolesList;

    // константы с алиасами колонок, используемых в выборках других модулей
    // (для случаев, когда проводится интеграция с другими бд)
    // оригинальное_имя = 'имя_в_новой_таблице'
    const FIRST_NAME = 'first_name';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['first_name', 'required'],
            ['first_name, last_name, middle_name', 'match',
                'pattern' => '/^[a-zA-Zа-яА-Я0-9_\- ]+$/u',
                'message' => 'Поле содержит не допустимые знаки.'
            ],
            ['is_active', 'boolean'],
            ['first_name, last_name, middle_name', 'length', 'max' => 30],
            ['email', 'length', 'max' => 75],
            ['email', 'email'],
            ['email', 'unique'],
            ['bio, role', 'safe'],

            ['uploadedPhoto', 'file',
                'allowEmpty' => true,
                'types' => 'jpg, gif, png',
                'maxSize' => 10485760, // 10mb
            ],

            ['birthdate', 'match', 'pattern' => '/\d{2}\.\d{2}\.\d{4}/'],

            ['is_active', 'default', 'value' => 0],
            //['date_joined','default','value'=>time(), 'on'=>'insert'],

            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, first_name, last_name, email, is_active, last_login, date_joined', 'safe', 'on' => 'search'],
        ];
    }

    public function scopes()
    {
        return [
            'inactive' => [
                'condition' => 'is_active=0',
            ],
        ];
    }

    /**
     * Сохраняем ip юзера
     * Обновляем даты
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if (preg_match('/\d{2}\.\d{2}\.\d{4}/', $this->birthdate)) {
                $this->birthdate = implode('-', array_reverse(explode('.', $this->birthdate)));
            } elseif (!preg_match('/\d{4}\-\d{2}\-\d{2}/', $this->birthdate)) {
                $this->birthdate = new \CDbExpression('NULL');
            }

            if ($this->isNewRecord) {
                $this->date_joined = new \CDbExpression('NOW()');
            }

            return true;
        } else {
            return false;
        }

    }

    protected function afterSave()
    {
        parent::afterSave();
        if ($this->isNewRecord) {
            // Если у нас новая запись,
            // то мы после insert сразу обновляем ее содержимое,
            // что бы получить актуальные даты для хэша
            $this->refresh();
        }
        $this->birthdate = date('d.m.Y', strtotime($this->birthdate));
    }

    protected function afterFind()
    {
        parent::afterFind();
        $this->birthdate = date('d.m.Y', strtotime($this->birthdate));
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'shopRatings' => [self::HAS_MANY, 'ShopRating', 'user_id'],
            'address' => [self::HAS_MANY, 'OrderAddress', 'user_id'],
            'roles' => [self::HAS_MANY, 'AuthAssignment', ['userid' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'photo' => 'Фото',
            'uploadedPhoto' => 'Фото',
            'bio' => 'Расскажите о себе',
            'birthdate' => 'Дата рождения',
            'fullName' => 'Имя и фамилия',
            'email' => 'Email',
            'emailWithStatus' => 'Email',
            'password' => 'Password',
            'is_active' => 'Is Active',
            'last_login' => 'Последний вход',
            'date_joined' => 'Дата регистрации',
            'role' => 'Кто вы?',
            'roles' => 'Роли (группы)',
        ];
    }

    public function getPhotoUrl()
    {
        return 'http://placekitten.com/g/150/200';
    }

    /**
     * Возвращает hash для подтверждения на основе $suff
     */
    protected function confirmationHash($suff)
    {
        return md5($this->id . $this->first_name . $this->date_joined . $suff);
    }

    /**
     * Возвращает url для подтверждения для действия $action
     */
    protected function confirmationUrl($action)
    {
        return \Yii::app()->createAbsoluteUrl('user/' . $action, ['h' => $this->{$action . 'Hash'}, 'email' => $this->email]);
    }

    /**
     * Возвращает url для подтверждения email
     */
    public function getConfirmUrl()
    {
        return $this->confirmationUrl('confirm');
    }

    /**
     * Возвращает hash для подтверждения email
     */
    public function getConfirmHash()
    {
        return $this->confirmationHash('mail');
    }

    /**
     * Возвращает url для подтверждения смены пароля
     */
    public function getChpassUrl()
    {
        return $this->confirmationUrl('chpass');
    }

    /**
     * Возвращает hash для подтверждения смены пароля
     */
    public function getChpassHash()
    {
        return $this->confirmationHash('psw');
    }

    /**
     * Отправляет письмо пользователю
     */
    public function mail($view, $subject)
    {
        if (is_string($view)) {
            $view = [$view];
        }

        $view['user'] = $this;

        $to = $this->email;

        self::mailInternal($view, $subject, $to);
    }

    /**
     * Внутренний метод для отправки емейлов.
     *
     * @param array $view массив вида
     *        array(
     *          0=>'название вьюхи',
     *          ['param1' => 'параметр, который передастся вьхе',
     *          'param2' => '...',
     *           ...]
     *        )
     * @param sting $subject тема письма
     * @param mixed $from от кого письмо. либо строка с емейлом,
     * либо массив array('email' => 'Имя отпарвляющего').
     * По умолчанию - noReplyEmail => shortName из настроек hamster.
     * @param mixed $to строка или массив емейлов получателей. По умолчанию - adminEmail из настроек hamster.
     * @static
     * @access protected
     * @return void
     */
    protected static function mailInternal($view, $subject, $to = false, $from = false)
    {
        $message = new \YiiMailMessage();

        if (!is_array($view)) {
            $view = [$view];
        }

        list($view, $params) = [array_shift($view), $view];

        if (!$from) {
            $from = [\Yii::app()->params['noReplyEmail'] => \Yii::app()->params['shortName']];
        }

        if (!$to) {
            $to = \Yii::app()->params['adminEmail'];
        }

        if (is_string($to)) {
            $to = [$to];
        }

        $message->view = $view;
        //userModel is passed to the view
        $message->setBody($params, 'text/html');
        foreach ($to as $email) {
            $message->addTo($email);
        }

        $message->from = $from;
        $message->subject = $subject;
        \Yii::app()->mail->send($message);
    }

    /**
     * Отправляет письмо админу
     *
     * @see {@link User::mailInternal()}
     * @static
     * @access public
     * @return void
     */
    public static function mailAdmin($view, $subject, $to = false, $from = false)
    {
        self::mailInternal($view, $subject, $to, $from);
    }

    /**
     * Отправляет письмо для подтверждения Email адреса
     */
    public function sendMailConfirm()
    {
        $this->mail('//mail/confirmMail', 'Активация аккаунта на ' . \Yii::app()->params['shortName']);
    }

    /**
     * Отправляет письмо для подтверждения Email адреса
     */
    public function sendChpassMail()
    {
        $this->mail('//mail/changePassword', 'Смена пароля на ' . \Yii::app()->params['shortName']);
    }

    /**
     * Возвращает модель User по его email
     */
    public function findByEmail($email = false)
    {
        if (!$email) {
            $email = \Yii::app()->user->email;
        }

        return $this->findByAttributes(['email' => $email]);
    }

    /**
     * @return array типы полей для форм администрирования модуля
     */
    public function getFieldTypes()
    {
        return [
            'role' => [
                'radiolist',
                'items' => $this->rolesList,
            ],
            'first_name' => 'text',
            'last_name' => 'text',
            'email' => 'text',
        ];
    }

    /**
     * В том случае если сценарий register,
     * добавляет аттрибут role в безопастные,
     * что бы он отображался в форме
     *
     * @access public
     * @return void
     */
    public function afterConstruct()
    {
        parent::afterConstruct();
        if ($this->scenario == 'register') {
            if (count($this->rolesList)) {
                // если есть роли для выбора, добавляем их в наш массивчик
                $validators = $this->getValidatorList();

                $validator = \CValidator::createValidator('safe', $this, 'role');
                $validators->add($validator);
            }
        }
    }

    /**
     * Создает список ролей для radiolist (если такие имеются)
     *
     * @access public
     * @return void
     */
    public function getRolesList()
    {
        if (!isset($this->_rolesList)) {
            $rolesList = [];
            // список групп, которые можно выбирать при регистрации
            $roles = \Yii::app()->authManager->getAuthItems(AuthItem::TYPE_ROLE);
            foreach ($roles as $id => $role) {
                if ($role->data['showOnRegister']) {
                    $rolesList[$id] = $id;
                }

            }

            $this->_rolesList = $rolesList;
        }
        return $this->_rolesList;
    }

    /**
     * @access public
     * @return string полное имя и фималилию пользвателя
     */
    public function getFullName()
    {
        $fullName = $this->first_name;
        if (!empty($this->last_name)) {
            $fullName .= ' ' . $this->last_name;
        }

        return $fullName;
    }

    /**
     * @access public
     * @return string возвращает html строку с стилем в зависимости от подвтержденности емейла пользователя
     */
    public function getEmailWithStatus()
    {
        return '<span class="status_' . ($this->is_active ? "3" : "1") . '">' . \CHtml::encode($this->email) . '</span>';
    }

    /**
     * @return boolean Возвращает статус активации емейла пользователя
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Меню управления ролями (для использования только в админ экшене)
     *
     * @access public
     * @return string роли текущего юзера
     */
    public function getRolesControll()
    {
        $roles = '';
        foreach ($this->roles as $role) {
            $roles .= '<div class="tagControll" data-roleid="' . $role->itemname . '" data-userid="' . $this->primaryKey . '">' . $role->l10edName . '<a href="" class="icon_delete roleRevoke"></a></div>';
        }
        $roles .= '<div><a href="" class="icon_add icon_label roleAssign" data-id="' . $this->primaryKey . '">Добавить роль</a></div>';
        return $roles;
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
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
        return '{{user}}';
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new \CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('first_name', $this->first_name, true);
        $criteria->compare('last_name', $this->last_name, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('is_active', $this->is_active);
        $criteria->compare('last_login', $this->last_login, true);
        $criteria->compare('date_joined', $this->date_joined, true);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }
}
