<?php
ob_start();
class LoginFormTest extends CDbTestCase
{
  protected $fixtures=array(
    'users'=>'User',
  );

  public function testLogin()
  {
    $user = $this->users('user1');

    $model=new LoginForm;
    
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

    /*
    $user = new User('register');
    $login = 'test@test.com';
    $password = 'qwertyu';
    $user->setAttributes(array(
      'first_name' => 'Тестовый юзер',
      'email' => $login,
      'password1' => $password,
      'password2' => $password,
    ));
    $this->assertTrue($user->save());

    // тест на то, что хэш все время остается идентичным
    $this->assertEquals($user->confirmUrl, $user->confirmUrl);
    $hash = $user->confirmHash;

	  $user = User::model()->findByEmail($login);
    $this->assertFalse((boolean)$user->is_active);
    
    // тест на то, что хэш все время остается идентичным
    $this->assertEquals($hash, $user->confirmHash);

    $user->is_active = 1;
    $this->assertTrue($user->save());
     */
  }

  /**
   * Просто выводим буффер (баг с отправкой заголовков до того, как начнется сессия)
   */
  public function lastTest()
  {
    echo ob_end_clean();
  }
}
