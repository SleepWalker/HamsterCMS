<?php

/**
 * This is the model class for table "AuthItem".
 *
 * The followings are the available columns in table 'AuthItem':
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property AuthAssignment[] $authAssignments
 * @property AuthItemChild[] $authItemchildren
 * @property AuthItemChild[] $authItemchildren1
 */

use user\models\User;

class AuthItem extends CActiveRecord
{
    /**
     * @property boolean $showOnRegister флаг, отвечающий за
     *    возможность выбора роли при регистрации пользователя
     */
    public $showOnRegister;

    /**
     * @property array $namesI18n массив с переводом идентификаторов стандартных ролей на русский язык
     */
    public static $namesI18n = array(
        'user' => 'Пользователь',
        'admin' => 'Администратор',
        'staff' => 'Контент менеджер',
        'guest' => 'Гость',
        'transfer' => 'Ожидающий переноса',
    );

    /**
     * @property CAuthItem $ai
     */
    protected $ai;

    // Константы типов элементов авторизации
    const TYPE_OPERATION = 0;
    const TYPE_TASK = 1;
    const TYPE_ROLE = 2;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return AuthItem the static model class
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
        return 'AuthItem';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, type', 'required'),
            array('type', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 64),
            //array('bizrule, data', 'default', 'value' => new CDbExpression('NULL'), 'setOnEmpty' => true),
            array('bizrule, description, data, showOnRegister', 'safe'),
            array('name', 'unique'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('name, type, description, bizrule, data', 'safe', 'on' => 'search'),
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
            'authAssignments' => array(self::HAS_MANY, 'AuthAssignment', 'itemname'),
            'authItemchildren' => array(self::HAS_MANY, 'AuthItemChild', 'parent'),
            'authItemchildren1' => array(self::HAS_MANY, 'AuthItemChild', 'child'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name' => 'Имя',
            'l10edName' => 'Имя',
            'type' => 'Тип',
            'description' => 'Описание',
            'bizrule' => 'Бизнес правило',
            'data' => 'Дополнительные данные',
            'showOnRegister' => 'Дать возможность выбора данной группы при регистрации',
        );
    }

    public function getTypeLabel()
    {
        $map = [
            self::TYPE_OPERATION => 'Операция',
            self::TYPE_TASK => 'Задача',
            self::TYPE_ROLE => 'Роль',
        ];

        return isset($map[$this->type]) ? $map[$this->type] : null;
    }

    /**
     * Возвращает элемент авторизации по его Pk (name)
     *
     * @param mixed $authItemId
     * @access public
     * @return void
     */
    public function findByPk($authItemId, $condition = '', $params = array())
    {
        $this->ai = $this->am->getAuthItem($authItemId);
        $this->setAttributes(array(
            'name' => $this->ai->name,
            'type' => $this->ai->type,
            'description' => $this->ai->description,
            'bizrule' => $this->ai->bizRule,
            'data' => $this->ai->data,
            'showOnRegister' => $this->ai->data['showOnRegister'],
        ));

        $this->oldPrimaryKey = $this->name;
        return $this;
    }

    /**
     * Список элементов авторизации (для использования, к примеру в DropDownList)
     *
     * @static
     * @access public
     * @return array для использования, к примеру в DropDownList
     */
    public static function getAuthItemsList()
    {
        $models = self::model()->findAll();
        $return = array();
        foreach ($models as $model) {
            $return[$model->primaryKey] = $model->l10edName;
        }

        return $return;
    }

    /**
     * Список ролей (для использования, к примеру в DropDownList)
     *
     * @static
     * @access public
     * @return array для использования, к примеру в DropDownList
     */
    public static function getRolesList()
    {
        $models = self::model()->findAllByAttributes(array('type' => AuthItem::TYPE_ROLE));
        $return = array();
        foreach ($models as $model) {
            $return[$model->primaryKey] = $model->l10edName;
        }

        return $return;
    }

    /**
     * Переводит стандартные названия ролей на русский язык
     *
     * @access public
     * @return void
     */
    public function getL10edName()
    {
        return in_array($this->primaryKey, array_keys(self::$namesI18n))
        ? self::$namesI18n[$this->primaryKey]
        : $this->primaryKey;
    }

    /**
     * Возвращает массив с дополнительными настройками роли
     *
     * @access protected
     * @return array
     */
    protected function getData()
    {
        if ($this->type == AuthItem::TYPE_ROLE && $this->showOnRegister) {
            $data['showOnRegister'] = $this->showOnRegister;
        }

        return $data ? $data : null;
    }

    public function save($runValidation = true, $attributes = null)
    {
        if (!$runValidation || $this->validate()) {
            if ($this->isNewRecord) {
                $params = array(
                    $this->name,
                    $this->type,
                    $this->description,
                    $this->bizrule,
                    $this->getData(),
                );
                // создаем новый экземпляр CAuthItem
                $this->ai = call_user_func_array(array($this->am, "createAuthItem"), $params);
            } else {
                $this->ai->name = $this->name;
                //$this->ai->type = $_POST['this->ai']['type'];
                $this->ai->description = $this->description;
                $this->ai->bizRule = $this->bizrule;
                $this->ai->data = $this->getData();
            }

            if (empty($this->ai->data)) {
                $this->ai->data = null;
            }

            if (empty($this->ai->bizRule)) {
                $this->ai->bizRule = new CDbExpression('NULL');
            }

            $this->am->saveAuthItem($this->ai, $this->oldPrimaryKey);
            return true;
        } else {
            return false;
        }

    }

    /**
     * Добавляет пользователя с $uid в очередь для переноса
     * в группу $chosenRole.
     * Используется при регистрации нового пользователя,
     * когда ему дают право выбора группы (роли).
     *
     * @param CActiveRecord $user
     * @param string $choosenRole
     * @access public
     * @return void
     */
    public function addToTransfer(User $user, $chosenRole)
    {
        if (!in_array($chosenRole, $user->rolesList)) {
            throw new \InvalidArgumentException("The role $chosenRole does not exists");
        }

        $authAss = $this->am->assign('transfer', $user->primaryKey, null, [
            'chosenRole' => $chosenRole
        ]);

        // Отправляем емейл администратору
        User::mailAdmin([
            'application.modules.user.views.mail.transfer_admin',
            'user' => $user,
            'chosenRole' => $chosenRole,
        ], '[Новый пользователь] Запрос на перемещение в группу');
    }

    /**
     * Присваивает элемент авторизации пользователю $user
     *
     * @param User $user
     * @param string $itemName
     * @param mixed $bizRule
     * @param mixed $data
     * @access public
     * @return void
     */
    public function assign(User $user, $itemName, $bizRule = null, $data = null)
    {
        $this->am->assign($itemName, $user->primaryKey, $bizRule, $data);
    }

    /**
     * @return array типы полей для форм администрирования модуля
     */
    public function getFieldTypes()
    {
        return array(
            'name' => 'text',
            'type' => array(
                'dropdownlist',
                'items' => array('Операция', 'Задача', 'Роль'),
                'attributes' => array(
                    // поле тайп нельзя редактировать,
                    // после создания authitem оно только для чтения
                    'disabled' => isset($this->type),
                ),
            ),
            'showOnRegister' => 'checkbox',
            'description' => 'textareaTiny',
            //'bizrule' => 'textareaTiny',
            //'data' => 'textareaTiny',//'keyvalue',
        );
    }

    /**
     * @return CDbAuthManager
     */
    public function getAm()
    {
        return self::am();
    }

    /**
     * @return CDbAuthManager
     */
    public static function am()
    {
        return Yii::app()->authManager;
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

        $criteria->compare('name', $this->name, true);
        $criteria->compare('type', $this->type);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('bizrule', $this->bizrule, true);
        $criteria->compare('data', $this->data, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
