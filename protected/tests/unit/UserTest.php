<?php
class UserTest extends CDbTestCase
{
  public $fixtures=array(
    'users'=>'User',
  );

  public function testRegister()
  {
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
  }
}
