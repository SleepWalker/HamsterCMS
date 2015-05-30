<?php
Yii::import('application.modules.shop.models.*');
class ShopTest extends CDbTestCase
{
  public $fixtures=array(
    'shop'=>'Shop',
    'shop_brand'=>'Brand',
    'shop_categorie'=>'Categorie',
    'shop_char_shema'=>'CharShema',
    'shop_char'=>'Char',
  );

  public function testGetCharSubQuery()
  {
    //$this->assertTrue($user->save());

    // тест на то, что хэш все время остается идентичным
    //$this->assertEquals($user->confirmUrl, $user->confirmUrl);
  }
}
