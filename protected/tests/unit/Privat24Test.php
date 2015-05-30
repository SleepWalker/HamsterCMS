<?php
error_reporting(E_ALL & ~E_NOTICE);
Yii::import('cart.components.*');
class Privat24Test extends CDbTestCase
{
  /**
   *  Тестируем правильно ли составляются контрольные суммы
   *  Так же проверяем правильно ли создается, сохраняется, загружается обьект (методы createPayment(), load(), save()
   *  Для создания обьекта используется класс Emoney
   */
  public function testResultAction()
  {
    // создали платеж и сохранили его
    $Privat24 = Emoney::choose('Privat24')->createPayment(array(
      'orderNo'=>5,
      'desc'=>'Test desc',
      'amount'=>999.05,
    ))->save();
    
    $this->assertTrue($Privat24 instanceof EmoneyBase);
    
    $_POST = array(
      'payment' => array(
        'ext_details' => 5, // вот эта строка используется для определения какой обьект грузить
      ),
    );
    
    // проверяем работу функции анализа POST запроса
    $payment = Emoney::parsePOST();
    $this->assertEquals($payment['class'], 'Privat24');
    $this->assertEquals($payment['orderNo'], 5);
    
    // загружаем обьект из кэша из данных POST зпапроса
    $Privat24Cached = Emoney::chooseFromPost();
    
    $this->assertTrue($Privat24Cached instanceof EmoneyBase);
    
    // проверяем, что бы загруженный обьект совпадал с ранее сохраненным
    $this->assertEquals($Privat24, $Privat24Cached);
    
    /**********************************
    * Проверяем функции валидации заказа
    **********************************/
    parse_str('amt=' . $Privat24Cached->amount . '&ccy=' . $Privat24Cached->purse . '&details=' . $Privat24Cached->desc . '&ext_details=' . $Privat24Cached->orderNo . '&pay_way=privat24&order=abrakadabra&merchant=65982&state=test&date=130812174933&ref=test payment&sender_phone=+380930566039', $_POST['payment']);
    $_POST['signature'] = sha1(md5($_POST['payment'].$Privat24Cached->secretKey));
    
    // загружаем обьект из кэша из данных POST запроса
    Emoney::chooseFromPost()->onSuccess(array($this, 'successHandler'))->resultAction();
    
    // проверка случая, когда приват отсылает запрос на successUrl
    parse_str('state=ok&amt=' . $Privat24Cached->amount . '&ccy=' . $Privat24Cached->purse . '&details=' . $Privat24Cached->desc . '&ext_details=' . $Privat24Cached->orderNo . '&pay_way=privat24&order=abrakadabra&merchant=65982&date=130812174933&ref=test payment&sender_phone=+380930566039', $_POST['payment']);
    $_POST['signature'] = sha1(md5($_POST['payment'].$Privat24Cached->secretKey));
    Emoney::chooseFromPost()->successAction();
    $this->expectOutputString('[EV]');
  }
  
  /**
   *  Проверка, передается ли ID заказа в событии
   */
  public function successHandler($event)
  {
    $this->assertEquals($event->sender->orderNo, 5);
    echo '[EV]';
  }
}