<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */

namespace user\models;

use user\components\UserIdentity;

class LoginForm extends \CFormModel
{
    public $user_email;
    public $user_password;
    public $rememberMe = 1;

    private $_identity;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            ['user_email, user_password', 'required'],
            ['user_email', 'email'],
            ['rememberMe', 'boolean'],
            ['user_password', 'authenticate'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'rememberMe' => 'Запомнить меня',
            'user_email' => 'Email',
            'user_password' => 'Пароль',
        ];
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_identity = new UserIdentity($this->user_email, $this->user_password);

            if (!$this->_identity->authenticate()) {
                $this->addError('user_password', 'Не правильный Email или пароль.');
            }

        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->user_email, $this->user_password);
            $this->_identity->authenticate();
        }

        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            \Yii::app()->user->login($this->_identity, $duration);
            return true;
        } else {
            return false;
        }

    }
}
