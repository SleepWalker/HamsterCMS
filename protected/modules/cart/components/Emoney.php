<?php
/**
 * Класс для инициализации компонента Emoney
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.cart.components.Emoney
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Emoney 
{
  protected $_moneyInstance;
  
  /**
   *  Выбор мерчанта и создание экземпляра его обьекта
   */
  public static function choose($moneyClass)
  {
    Yii::import('cart.components.emoney.'.$moneyClass);
    if($moneyClass != 'EmoneyNull' && !is_subclass_of($moneyClass, 'EmoneyBase'))
      throw new Exception("Класс {$moneyClass} должен наследовать интерфейс EmoneyBase");
    
    // Создаем класс для обслуживания электронной валюты и заполняем его параметрами
    $moneyInstance = new $moneyClass;

    $cartEmoneyParams = Yii::app()->modules['cart']['params']['emoney'];
    if(isset($cartEmoneyParams) && $cartEmoneyParams[$moneyClass]['active'] == true)
    {
      // Задаем кошелек для операций
      $moneyInstance->purse = $cartEmoneyParams[$moneyClass]['purse'];
      // Задаем секретный ключ
      $moneyInstance->secretKey = $cartEmoneyParams[$moneyClass]['secretKey'];
    }

    return $moneyInstance;
  }
  
  /**
   *  Выбор мерчанта и создание экземпляра его обьекта на основе данных в массиве $_POST
   */
  public static function chooseFromPOST()
  {
    $payment = self::parsePOST();
    
    if(empty($payment['class']) || empty($payment['orderNo']))
    // переадресовываем все обращения на обьект-пустышку
      $payment['class'] = 'EmoneyNull';

    return self::choose($payment['class'])->load($payment['orderNo']);
  }
  
  /**
   *  Анализирует $_POST и определяет от какого мерчанта поступил запрос. 
   *  $POSTtoMoney - массив соответствия переменная пост с номером заказа => название класса мерчанта (по сути структура этого массива повторяет структуру POST запроса)
   *                 возможен формат со вложенными массивами (в этом формате проводится проверка элементов массива $_POST,
   *                 если они окажутся не массивом в тех случаях, когда должны были, будет предпринята попытка конвертирования с помощью parse_str()).
   *  Пример:
   *    $POSTtoMoney = array(
   *      'LMI_PAYMENT_NO' => 'WM',  // вернет номер заказа из $_POST['LMI_PAYMENT_NO'] и передаст его на обработку классу WM
   *      'payment' => array(
   *        'order' => 'Privat24',  // вернет номер заказа из $_POST['payment']['order'] (предварительно преобразовав $_POST['payment'] в массив, если нужно) и передаст его на обработку классу Privat24
   *      ),
   *    );
   *
   *  @return название класса мерчанта и номер заказа
   */ 
  public static function parsePOST()
  {
    $POSTtoMoney = array(
      'LMI_PAYMENT_NO' => 'WM',
      'payment' => array(
        'ext_details' => 'Privat24',
      ),
    );
    
    foreach($POSTtoMoney as $orderNumName => $moneyClass)
    {
      if(!isset($_POST[$orderNumName])) continue;
      if(is_array($moneyClass))
      {
        // если $_POST[$orderNumName] - не массив - преобразовываем его в массив.
        // (значит он в запросе находился в виде строки name1=value1&name2=value2.... (так к примеру на privat24))
        if(!is_array($_POST[$orderNumName]))
          parse_str($_POST[$orderNumName], $_POST[$orderNumName]);
          
        $orderNo = $_POST[$orderNumName][key($moneyClass)];
        $moneyClass = $moneyClass[key($moneyClass)];
      }else
        $orderNo = $_POST[$orderNumName];
      break;
    }
    
    return array('class' => $moneyClass, 'orderNo' => $orderNo);
  }
}
?>
