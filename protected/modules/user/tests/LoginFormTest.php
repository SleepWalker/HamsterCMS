<?php
use user\models\LoginForm;
use user\models\User;

class LoginFormTest extends CTestCase
{
    public function xtestLogin()
    {
        $user = $this->users('user1');

        $model = new LoginForm;

        $model->user_email = $user->email;
        $model->user_password = 'persspiku';

        $this->assertTrue($model->validate());
        $this->assertTrue($model->login());

        $this->assertEquals($user->email, Yii::app()->user->getState('email'));

        // Проверяем права
        $this->assertTrue(Yii::app()->user->checkAccess('user'));
        $this->assertFalse(Yii::app()->user->checkAccess('guest'));

        Yii::app()->user->logout();

        $this->assertNotEquals($user->email, Yii::app()->user->getState('email'));
        $this->assertTrue(Yii::app()->user->isGuest);
        $this->assertFalse(Yii::app()->user->checkAccess('user'));
        $this->assertTrue(Yii::app()->user->checkAccess('guest'));
    }
}
