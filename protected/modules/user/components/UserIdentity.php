<?php
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */

namespace user\components;

use user\models\User;
use user\models\Identity;
use user\components\PasswordHash;

class UserIdentity extends \CUserIdentity
{
    public $user;
    private $identity;

    public function __construct($email, $password)
    {
        parent::__construct($email, $password);

        $this->identity = Identity::model()->findIdentity($email);
        if ($this->identity) {
            $this->user = $this->identity->user;
        }
    }

    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        if (!$this->user || !$this->identity) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            $hash = new PasswordHash($this->identity->private);

            if (!$hash->verify($this->password)) {
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            } else {
                $this->setState('email', $this->user->email);

                $this->errorCode = self::ERROR_NONE;

                $this->user->last_login = new \CDbExpression('NOW()');
                $this->user->save();
            }
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
