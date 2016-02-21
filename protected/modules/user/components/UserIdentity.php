<?php
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */

namespace user\components;

use user\models\User;

class UserIdentity extends \CUserIdentity
{
    public $user;

    public function __construct($username, $password = null)
    {
        parent::__construct($username, $password);
        $this->user = User::model()->findByEmail($this->username);

        if ($this->password === null) {
            $this->errorCode = self::ERROR_NONE;
        }
    }

    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        if ($this->user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else if (!$this->user->validatePassword($this->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->setState('email', $this->user->email);

            $this->errorCode = self::ERROR_NONE;

            // Обновляем дату последнего входа
            $this->user->save();
        }
        return $this->errorCode == self::ERROR_NONE;
    }

    public function getId()
    {
        return $this->user->primaryKey;
    }

    public function getName()
    {
        return $this->user->first_name;
    }
}
