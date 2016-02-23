<?php
/**
 * This is an abstract model for user identity data.
 * It is designed to handle both login-password and oauth authorization.
 *
 * The followings are the available columns in table 'user_identity':
 * @property string $id
 * @property string $user_id
 * @property string $provider
 * @property string $public
 * @property string $private
 *
 * The followings are the available model relations:
 * @property User $user
 */

namespace user\models;

use user\models\User;
use KoKoKo\assert\Assert;

class Identity extends \CActiveRecord
{
    const PROVIDER_DEFAULT = 'default';

    /**
     * @param  string $public
     * @param  string $provider
     *
     * @return Identity
     */
    public function findIdentity($public, $provider = self::PROVIDER_DEFAULT)
    {
        return $this->findByAttributes([
            'public' => $public,
            'provider' => $provider,
        ]);
    }

    protected function beforeSave()
    {
        Assert::assert($this->user_id, 'user_id')->numeric();
        Assert::assert($this->provider, 'provider')->notEmpty()->string();
        Assert::assert($this->public, 'public')->notEmpty();
        Assert::assert($this->private, 'private')->notEmpty();

        return parent::beforeSave();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'user' => [self::BELONGS_TO, User::class, 'user_id'],
        ];
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user_identity';
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
}
