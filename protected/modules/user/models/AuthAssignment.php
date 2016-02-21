<?php

/**
 * This is the model class for table "AuthAssignment".
 *
 * The followings are the available columns in table 'AuthAssignment':
 * @property string $itemname
 * @property string $userid
 * @property string $bizrule
 * @property string $data
 *
 * The followings are the available model relations:
 * @property AuthItem $itemname0
 */

use user\models\User;

class AuthAssignment extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return AuthAssignment the static model class
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
        return 'AuthAssignment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('itemname, userid', 'required'),
            array('itemname, userid', 'length', 'max' => 64),
            array('bizrule, data', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('itemname, userid, bizrule, data', 'safe', 'on' => 'search'),
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
            'am' => array(self::BELONGS_TO, 'AuthItem', 'itemname'),
            'user' => array(self::BELONGS_TO, User::class, 'userid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'itemname' => 'Itemname',
            'userid' => 'Userid',
            'bizrule' => 'Bizrule',
            'data' => 'Data',
            'name' => 'Имя пользователя',
        );
    }

    public function findAllByRole($roles)
    {
        // группа user (пользователи) у нас автоматически присваивается,
        // потому нам нужно вручную выбрать всех пользователей
        // выбирать юзеров остальных ролей нету смысла, так как в группу user
        // входят все зарегестрированные пользователи
        if (is_array($roles) && in_array('user', $roles) || $roles == 'user') {
            $users = User::model()->findAll();
            $models = array();
            foreach ($users as $i => $user) {
                $models[$i] = new self;
                $models[$i]->setAttributes(array(
                    'itemname' => 'user',
                    'userid' => $user->primaryKey,
                    'user' => $user,
                ));
            }
            return $models;
        } else {
            return self::model()->with('user')->findAllByAttributes(array('itemname' => $roles));
        }

    }

    public function afterFind()
    {
        $this->data = unserialize($this->data);
    }

    public function getTransferCount()
    {
        $sql = "SELECT COUNT(*) as transferCount FROM " . $this->tableName() . " WHERE itemname='transfer'";
        $command = Yii::app()->db->createCommand($sql);
        $results = $command->queryAll();
        return (int) $results[0]["transferCount"];
    }

    public function getName()
    {
        return $this->user->first_name . ' ' . $this->user->last_name;
    }

    public function getEmail()
    {
        return $this->user->email;
    }

    /**
     * Переводит стандартные названия ролей на русский язык
     *
     * @access public
     * @return void
     */
    public function getL10edName()
    {
        return in_array($this->itemname, array_keys(AuthItem::$namesI18n))
        ? AuthItem::$namesI18n[$this->itemname]
        : $this->itemname;
    }

    /**
     * Выполняет обработку трансфера пользователя в выбранную им роль в зависимости от $assign
     *
     * @param boolean $assign если true пользователь будет перемещен в выбранную им роль
     * @access public
     * @return AuthAssignment модель с информацией до трансфера
     */
    public function transfer($userid, $assign)
    {
        $aa = AuthAssignment::model()->findByPk(array('userid' => $userid, 'itemname' => 'transfer'));

        $role = $aa->data['chosenRole'];
        if ($assign) {
            AuthItem::am()->assign($role, $aa->userid);
        }

        AuthItem::am()->revoke('transfer', $aa->userid);

        $aa->user->mail(array(
            'application.modules.user.views.mail.transfer_user',
            'chosenRole' => $role,
            'accepted' => $assign,
            'user' => $aa->user,
        ), Yii::app()->params['shortName']);

        return $aa;
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

        $criteria->compare('itemname', $this->itemname, true);
        $criteria->compare('userid', $this->userid, true);
        $criteria->compare('bizrule', $this->bizrule, true);
        $criteria->compare('data', $this->data, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
