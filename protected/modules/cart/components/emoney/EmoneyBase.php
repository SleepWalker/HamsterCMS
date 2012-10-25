<?php
/**
 *  Базовый класс-интерфейс для работы с различными мерчантами
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.components.emoney.EmoneyBase
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class EmoneyBase extends CComponent
//CComponent используется в основном для того, что бы был доступ к геттеру и сеттеру Yii, а так же для событий
{
  // Адреса на которые будут посылаться запросы мерчантами
  public $resultUrl;
  public $successUrl;
  public $failUrl;
  
  // Номер заказа
  public $orderNo;
  
  // сумма заказа
  public $amount;
  
  // описание заказа
  public $desc;
  
  // кошелек/id продавца
  public $purse;
  
  // секретный ключ (используется для валидации хэш сумм)
  public $secretKey;
  
  // поля для формы
  protected $_fields;
  
  // поля для формы
  protected $_paymentInfo;
  
  // индиактор, что валидация - пройдена
  protected $_valid;
  
  // переменная для хранения хэша (что бы потом его можно было залогировать)
  protected $_hash;
  
  /**
   *  Событие, вызываемое в случае успешной валидации оплаты
   */
  final public function onSuccess($event) 
  {
    // если $event не является инстансом CEvent задаем обработчик события
    if(!($event instanceof CEvent))
    {
      $this->attachEventHandler('onSuccess', $event);
      return $this;
    }
    $this->raiseEvent('onSuccess', $event);
  }
  
  /**
   *  Инициирует свойства обьекта значениями из ассоциативного массива $options
   */
  public function init(array $options)
  {
    foreach($options as $opName =>  $opVal)
        $this->$opName = $opVal;
  }
  
  /**
  *  Создает платеж
  *  Возвращает массив скрытых полей и action для HTML формы
  *  Массив с информацией о заказе
  **/
  abstract protected function _createPayment(array $orderInfo);
  //обертка для _createPayment для обеспечения chaining методов
  final public function createPayment(array $orderInfo) {
    $this->_createPayment($orderInfo);
    $this->log('Создан счет №'.$this->orderNo);
    return $this;
  }
  
  
  /**
  * Сохраняет данные пост запроса и данные с инфой о запросе в лог
  **/
  final public function log($message = 'none')
  {
    Yii::log(
      "message: " . 
      $message . "\n\n\n" .
      "paymentInfo:\n" . 
      CVarDumper::dumpAsString($this->paymentInfo) . "\n\n\n" .
      "POST:\n" .
      CVarDumper::dumpAsString($_POST) . "\n\n\n" .
      "HASH:\n" .
      $this->_hash . "\n\n\n" .
      "VALID: " . ($this->isValid ? 'true' : 'false') . "\n\n", 'info', 'Emoney.'.get_class($this));
  }
  
  /**
  * Сохраняет обьект в кэш
  **/
  final public function save()
  {
    Yii::app()->cache->set('Emoney.' . get_class($this) . '.' . $this->orderNo, get_object_vars($this), 60*30); // кешируем на пол часа
    return $this;
  }
  
  /**
  * Загружает обьект из кэша
  **/
  final public function load($orderNo)
  {
    $options = Yii::app()->cache->get('Emoney.' . get_class($this) . '.' . $orderNo);
    if (!$options)
      $this->sendError('Ошибка! Пользовательская сессия истекла');
    $this->init($options);
    return $this;
  }
  
  /**
  * Очищает кэш
  **/
  final public function delete()
  {
    Yii::app()->cache->delete('Emoney.' . get_class($this) . '.' . $this->orderNo); 
    return $this;
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как ошибку в заказе
  **/
  protected function sendError($message) {
    $this->log($message);
    Yii::app()->end();
  }
  
  /**
  * Отправляет ответ, который мерчант воспримет как сообщение об истинности данных заказа
  **/
  abstract protected function sendSuccess($params = '');
  
  /**
  * Действия, которые необходимо выполнить на адресе resultUrl (обычно валидация хэш сумм)
  **/
  abstract protected function _resultAction();
  //обертка для _resultAction для обеспечения chaining методов
  final public function resultAction() {
    $this->_resultAction();
    return $this;
  }
  
  /**
  * Действия, которые необходимо выполнить перед тем, как переадресовывать юзера на failUrl
  **/
  abstract public function failAction();
  
  /**
  * Действия, которые необходимо выполнить перед тем, как переадресовывать юзера на successUrl
  * К примеру запись заказа в БД, очистка сессий и т.д.
  **/
  abstract public function successAction();
  
  /**
   *  Возвращает HTML код формы
   */
  public function getForm($submitValue = 'Перейти к оплате заказа')
  {
  }
  
  /**
   *  Возвращает массив с параметрами платежа (этот же массив используется для HTML формы платежа)
   */
  final protected function getPaymentInfo()
  {
    return $this->_paymentInfo;
  }
  
  /**
   *  Сеттер для массива с параметрами платежа
   */
  final protected function setPaymentInfo($value)
  {
    $this->_paymentInfo = $value;
  }
  
  /**
   *  Возвращает адрес action для отправки формы
   */
  abstract public function getFormAction();
  
  /**
   *  Возвращает HTML код полей для формы
   */
  public function getFormFields()
  {
    $this->_fields = '';
    foreach($this->paymentInfo as $name => $value)
      $this->_fields .= CHtml::hiddenField($name, $value); 
    return $this->_fields;
  }
  
  /**
   *  Геттер для переменной $_valid
   */
  final public function getIsValid()
  {
    return $this->_valid;
  }
  
  /**
   *  Вместо сеттера для $_valid
   */
  final protected function markAsValid($deleteCache = true)
  {
    $this->_valid = true;
    // сохраняем информацию в логе
    $this->log('Оплата счета №'.$this->orderNo);
    // Создаём экземпляр потомка CEvent
    $event = new CEvent($this);
    // Вызываем событие
    $this->onSuccess($event);
    // отправляем сообщение об успешной обработке данных
    $this->sendSuccess();
    
    if($deleteCache)
      // очищаем кеш
      $this->delete();
    else
      // скрипту еще нужна информация о заказе, потому сохраняем ее
      $this->save();
  }
  
  final protected function markInvalid()
  {
    $this->_valid = false;
  }
}
?>