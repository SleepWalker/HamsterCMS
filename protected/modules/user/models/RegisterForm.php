<?php
/**
 * The form for Login-Password registration
 */

namespace user\models;

use user\models\User;

class RegisterForm extends \CFormModel
{
    public $email;
    public $password1;
    public $password2;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['email, password1, password2', 'required'],
            ['password2', 'compare',
                'compareAttribute' => 'password1',
                'strict' => true
            ],
            ['password1, password2', 'length', 'min' => 7, 'max' => 128],
            ['email', 'length', 'max' => 75],
            ['email', 'email'],
            ['email', 'checkEmailAvailability'],
        ];
    }

    public function getPassword()
    {
        return $this->password1;
    }

    public function checkEmailAvailability($attribute, $params)
    {
        if (User::model()->findByEmail($this->email)) {
            $this->addError('email', 'Введенный email уже занят. Если это ваш email, воспользуйтесь формой входа или восстановлением пароля');
        }
    }

    public function resetPasswords()
    {
        $this->password1 = $this->password2 = '';
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'password1' => 'Пароль',
            'password2' => 'Пароль еще раз',
        ];
    }
}
