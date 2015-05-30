<?php
error_reporting(E_ALL & ~E_NOTICE);
Yii::import('cart.components.*');
class WMTest extends CDbTestCase
{
  /**
   *  Тестируем правильно ли составляются контрольные суммы
   *  Так же проверяем правильно ли создается, сохраняется, загружается обьект (методы createPayment(), load(), save()
   *  Для создания обьекта используется класс Emoney
   */
  public function testResultAction()
  {
    // создали платеж и сохранили его
    $WM = Emoney::choose('WM')->createPayment(array(
      'orderNo'=>5,
      'desc'=>'Test desc',
      'amount'=>999.05,
    ))->save();
    
    $this->assertTrue($WM instanceof EmoneyBase);
    
    $_POST = array(
      'LMI_PAYMENT_NO' => 5, // вот эта строка используется для определения какой обьект грузить
    );

    // проверяем работу функции анализа POST запроса
    $payment = Emoney::parsePOST();
    $this->assertEquals($payment['class'], 'WM');
    $this->assertEquals($payment['orderNo'], 5);
    
    // загружаем обьект из кэша из данных POST зпапроса
    $WMCached = Emoney::chooseFromPost();
    
    $this->assertTrue($WMCached instanceof EmoneyBase);
    
    // проверяем, что бы загруженный обьект совпадал с ранее сохраненным
    $this->assertEquals($WM, $WMCached);
    
    // Проверка работы логирования
    $WMCached->log('Log test message');
    
    /**********************************
    * Проверяем функции валидации заказа
    **********************************/
    // превалидация
    $_POST = array(
      'LMI_PAYER_WM' => 'LMI_PAYER_WM',
      'LMI_PAYER_PURSE' => 'LMI_PAYER_PURSE',
      'LMI_PAYMENT_NO' => 5,
      'LMI_PREREQUEST' => 1,
    );
    $WMCached->resultAction();
    
    // обработка хэш сумм
    $_POST = array(     
      'LMI_PAYMENT_NO' => 5,
      'LMI_SYS_INVS_NO' => 'LMI_SYS_INVS_NO',
      'LMI_SYS_TRANS_NO' => 'LMI_SYS_TRANS_NO',
      'LMI_SYS_TRANS_DATE' => 'LMI_SYS_TRANS_DATE',   
      'LMI_PAYMENT_AMOUNT' => $WMCached->amount,
      'LMI_MODE' => $WMCached->paymentInfo['LMI_MODE'],
      'LMI_PAYEE_PURSE' => $WMCached->paymentInfo['LMI_PAYEE_PURSE'],
      'LMI_HASH' => strtoupper(md5(
        $WMCached->paymentInfo['LMI_PAYEE_PURSE'].$WMCached->amount.$WMCached->orderNo.$WMCached->paymentInfo['LMI_MODE'].'LMI_SYS_INVS_NO'.'LMI_SYS_TRANS_NO'.'LMI_SYS_TRANS_DATE'.$WMCached->secretKey.'LMI_PAYER_PURSE'.'LMI_PAYER_WM'
      )),
    );
    
    // загружаем обьект из кэша из данных POST запроса
    Emoney::chooseFromPost()->onSuccess(array($this, 'successHandler'))->resultAction();
    
    // проверка работоспособности successAction при условии отсутствия POST запроса (тест класса EmoneyNull)
    unset($_POST);
    Emoney::chooseFromPost()->successAction();
    $this->expectOutputString('YES[EV]YES');
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