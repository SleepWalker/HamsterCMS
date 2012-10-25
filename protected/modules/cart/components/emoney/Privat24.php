<?php
/**
 *  Класс для работы с сервисом Privat24
 *  Документация по сервису: https://api.privatbank.ua/
 *
 * @author Sviatoslav Danylenko <mybox@udf.su>
 * @link http://udf.su/
 * @copyright Copyright &copy; 2012 udf.su
 * @version 1.0
 * @package Emoney
 */

Yii::import('cart.components.emoney.EmoneyBase');
Yii::import('application.vendors.*');
require_once('alphaID.inc.php');

class Privat24 extends EmoneyBase
{
  /**
  *  Создает платеж
  *  Возвращает массив скрытых полей и action для HTML формы
  **/
  protected function _createPayment(array $orderInfo)
  {
    if(is_array($orderInfo))
      foreach($orderInfo as $opName =>  $opVal)
        $this->$opName = $opVal;
    
    // страница для промежуточной обработки запросов
    $this->resultUrl = Yii::app()->createAbsoluteUrl('/cart/result');
    
    // страница успешной оплаты
    $this->successUrl = Yii::app()->createAbsoluteUrl('/cart/success');
    
    // страница не успешной оплаты
    $this->failUrl = Yii::app()->createAbsoluteUrl('/page/fail');
    
    $this->paymentInfo = array(
      'amt' => number_format($this->amount, 2, '.', ''), // Сумма заказа
      'details' => $this->desc, // Описание заказа
      'ext_details' => $this->orderNo, // Дополнительное описание (здесь мы сообщим реальный номер заказа)
      'order' => alphaID(uniqid()), // Номер заказа (уникальный, чисто для платежной системы)
      'ccy' => $this->purse, // валюта UAH / USD / EUR
      'server_url' => $this->resultUrl, // Cтраница, принимающая ответ API о результате платежа
      'return_url' => $this->successUrl, // Адрес, куда будет перенаправлен клиент после оплаты
      'merchant' => '65982', // id мерчанта
      'pay_way' => 'privat24', // способ оплаты
    );
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как ошибку в заказе
  **/
  protected function sendError($message)
  {
    throw new CHttpException(404, $message);
    parent::sendError($message);
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как сообщение об истинности данных заказа
  **/
  protected function sendSuccess($params = '') 
  {
  
  }
  
  /**
  * Проверка правильности данных заказа (обычно это resultURL)
  **/
  protected function _resultAction()
  {
    if ($hash = $_POST['signature'] && $_POST['payment'])
    {
      //'payment' => 'amt=349.00&ccy=UAH&details=Detail&ext_details=&pay_way=privat24&order=38&merchant=65982&state=test&date=130812174933&ref=test payment&sender_phone=+380930566039'
      $options = $this->paymentInfo;
      
      $this->_hash = sha1(md5($_POST['payment'].$options['privat24pass']));
      $valid = $hash == $this->_hash;
      
      // проверим совпадает ли сумма оплаты, id мерчанта и валюта
      $recievedPayment = $_POST['payment'];
      
      $valid = $valid && ($recievedPayment['amt'] == $options['amt']);
      $valid = $valid && ($recievedPayment['ccy'] == $options['ccy']);
      $valid = $valid && ($recievedPayment['merchant'] == $options['merchant']);      
    
      if($valid)
        // присваиваем обьекту пометку "валидный"
        $this->markAsValid(false);
      else
        $this->sendError('Не верные данные заказа');
    }
  }
  
  /**
  * Действия, которые необходимо выполнить перед тем, как переадресовывать юзера на failUrl
  **/
  public function failAction()
  {
  
  }
  
  /**
  * Действия, которые необходимо выполнить перед тем, как переадресовывать юзера на successUrl
  * К примеру запись заказа в БД, очистка сессий и т.д.
  **/
  public function successAction()
  {
    // privat24 может отсылать ответы для проверки оплаты и на этот урл, потому проводим валидацию и здесь
    if ($_POST['signature'] && $_POST['payment']) 
      if($_POST['payment']['state'] != 'ok')
        $this->markInvalid();
    
    // Чистим кэш
    $this->delete();
      
    // Так как приват перенаправляет на одну и ту же страницу, нам нужно решить о редиректе в этом методе
    if(!$this->isValid)
      Yii::app()->getRequest()->redirect($this->failUrl);
  }
  
  /**
   *  Возвращает адрес action для отправки формы
   */
  public function getFormAction()
  {
    return 'https://api.privatbank.ua/p24api/ishop';
  }
}
?>