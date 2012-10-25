<?php
/**
 *  Класс для работы с сервисом WebMoney Merchant
 *  Документация по сервису: http://wiki.webmoney.ru/projects/webmoney/wiki/Web_Merchant_Interface
 *
 * @author Sviatoslav Danylenko <mybox@udf.su>
 * @link http://udf.su/
 * @copyright Copyright &copy; 2012 udf.su
 * @version 1.0
 * @package Emoney
 */
//T!:Проверить, действительно ли данные переданы от сервиса Web Merchant Interface (Проверка источника данных)
Yii::import('cart.components.emoney.EmoneyBase');

class WM extends EmoneyBase
{
  /**
  *  Создает платеж
  *  Возвращает массив скрытых полей и action для HTML формы
  **/
  protected function _createPayment(array $orderInfo)
  {
    if(is_array($orderInfo))
      $this->init($orderInfo);
    
    // страница для промежуточной обработки запросов
    $this->resultUrl = Yii::app()->createAbsoluteUrl('/cart/result');
    
    // страница успешной оплаты
    $this->successUrl = Yii::app()->createAbsoluteUrl('/cart/success');
    
    // страница не успешной оплаты
    $this->failUrl = Yii::app()->createAbsoluteUrl('/page/fail');
    
    $this->paymentInfo = array(
      'LMI_PAYMENT_AMOUNT' => number_format($this->amount, 2, '.', ''), // Сумма заказа
      'LMI_PAYMENT_DESC_BASE64' => base64_encode($this->desc), // Описание заказа
      'LMI_PAYMENT_NO' => $this->orderNo, // Номер заказа
      'LMI_PAYEE_PURSE' => $this->purse, // Кошелек, на который поступят деньги
      //'LMI_SIM_MODE' => 0,  работает только в тестовом режиме: 0 - все тестовые платежи - успешные; 1 - неуспешные
      'LMI_MODE' => 0, // 1 - тестовый режим; 0 - продакшен режим
      'LMI_RESULT_URL' => $this->resultUrl, // промежуточная страница для валидации заказа
      'LMI_SUCCESS_URL' => $this->successUrl, // страница оповещения об успешном заказе
      'LMI_SUCCESS_METHOD' => 2, // Вызывать страницу success методом link (тоесть без доп. данных в запросе)
      'LMI_FAIL_URL' => $this->failUrl, // страница оповещения о неудачном заказе
      'LMI_FAIL_METHOD' => 2, // см. LMI_SUCCESS_METHOD
    );
  }
  
  /**
   *  Затираем описание заказа, так как WM почему-то шлет в нем кракозябры
   */
  public function init(array $options)
  {
    if($_POST['LMI_PAYMENT_DESC'])
      $_POST['LMI_PAYMENT_DESC'] = '';
    parent::init($options);
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как ошибку в заказе
  **/
  protected function sendError($message)
  {
    echo $message . "\n";
    parent::sendError($message);
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как сообщение об истинности данных заказа
  **/
  protected function sendSuccess($params = '')
  {
    echo 'YES';
  }
  
  /**
  * Проверка правильности данных заказа (обычно это resultURL)
  *
  * Порядок составления хэша:
  * - Кошелек продавца (LMI_PAYEE_PURSE);
  * - Сумма платежа (LMI_PAYMENT_AMOUNT);
  * - Внутренний номер покупки продавца (LMI_PAYMENT_NO);
  * - Флаг тестового режима (LMI_MODE);
  * - Внутренний номер счета в системе WebMoney Transfer (LMI_SYS_INVS_NO);
  * - Внутренний номер платежа в системе WebMoney Transfer (LMI_SYS_TRANS_NO);
  * - Дата и время выполнения платежа (LMI_SYS_TRANS_DATE);
  * - Secret Key (LMI_SECRET_KEY);
  * - Кошелек покупателя (LMI_PAYER_PURSE);
  * - WMId покупателя (LMI_PAYER_WM).
  * - Переведите результат в верхний регистр
  **/
  protected function _resultAction()
  {
    // Проверяем предварительный запрос
    if($_POST['LMI_PREREQUEST'])
    {
      // предварительная проверка правильности данных
      foreach($_POST as $name=>$value)
        if($this->paymentInfo[$name] && $this->paymentInfo[$name] != $value)
          $this->sendError('Ошибка в предварительной проверке данных платежа');
          
      // Сохраняем wmid и кошелек покупателя
      $options = $this->paymentInfo;
      $options['LMI_PAYER_WM'] = $_POST['LMI_PAYER_WM'];
      $options['LMI_PAYER_PURSE'] = $_POST['LMI_PAYER_PURSE'];
      $this->paymentInfo = $options;

      
      // Пересохраняем данные обьекта в кэше
      $this->save();
      $this->sendSuccess();
    }
    
    // обработка конечного запроса на result
    else if($hash = $_POST['LMI_HASH'])
    {
      $options = $this->paymentInfo;
      
      $this->_hash = strtoupper(md5(
        $options['LMI_PAYEE_PURSE'].
        $options['LMI_PAYMENT_AMOUNT'].
        $options['LMI_PAYMENT_NO'].
        $options['LMI_MODE'].
        $_POST['LMI_SYS_INVS_NO'].
        $_POST['LMI_SYS_TRANS_NO'].
        $_POST['LMI_SYS_TRANS_DATE'].
        $this->secretKey.
        $options['LMI_PAYER_PURSE'].
        $options['LMI_PAYER_WM']    
      ));      

      $valid = $hash == $this->_hash;
      $valid = ($_POST['LMI_PAYMENT_AMOUNT'] == $options['LMI_PAYMENT_AMOUNT']) && $valid;
      $valid = ($_POST['LMI_PAYEE_PURSE'] == $options['LMI_PAYEE_PURSE']) && $valid;
      $valid = ($_POST['LMI_MODE'] == $options['LMI_MODE']) && $valid;
      
      if($valid)
        // присваиваем обьекту пометку "валидный"
        $this->markAsValid();
      else
        $this->sendError('Не верные данные заказа');
    }
    else    
    $this->sendError('Неизвестная ошибка');
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
  
  }
  
  /**
   *  Возвращает адрес action для отправки формы
   */
  public function getFormAction()
  {
    return 'https://merchant.webmoney.ru/lmi/payment.asp';
  }
}
?>