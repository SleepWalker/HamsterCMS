<?php	
/**
 * Widget that displays user cart status
 * Renders link to cart and amount and price for goods in it
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.widgets.cartstatus.CartStatus
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
/**
*  Возвращает ссылку на корзину и колличество товаров в ней
**/
class CartStatus extends CWidget
{
  protected $assetsUrl;
  public function init()
  {
    // регестрируем assets
    $this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    $this->registerScriptFile('cartManage.js');
    
    $order = Yii::app()->session['order'];
    $summary = $order['summary'];
    
    echo '<div id="cartStatusWidget">';
	  if($order['cart'])
	  {
	    foreach($order['cart'] as $prod)
      {
        $summ += $prod->price * $prod->quantity;
        $amount += $prod->quantity;
      }
      $summ = number_format($summ, 2, ',', ' ');
     if($summary)
     {
       $summ = $summary['orderPrice'];
       $amount = $summary['prodCount'];
     }
	   echo '<a href="/cart/">В вашей корзине <b class="qtotal">' . $amount . '</b> ' . $this->pluralForm($amount, 'товар', 'товара', 'товаров') . ' на сумму <b class="sumtotal">' . $summ . ' грн.</b></a>';
    }	  
	  else
	   echo 'Ваша корзина';// <b>пустая</b>
	  echo '</div>';
  }
	
	/**
	*  Выбирает слово в правильном падеже в зависимости от величины числа $n
	*  Пример использования: $this->pluralForm(5, 'товар', 'товара', 'товаров')
	**/
	protected function pluralForm($n, $form1, $form2, $form5)
  {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $form5;
    if ($n1 > 1 && $n1 < 5) return $form2;
    if ($n1 == 1) return $form1;
    return $form5;
  }
  
  protected function registerScriptFile($fileName,$position=CClientScript::POS_END)
  {
    Yii::app()->getClientScript()->registerScriptFile($this->assetsUrl.'/js/'.$fileName,$position);
  }
  
  protected function registerCssFile($fileName)
  {
    Yii::app()->getClientScript()->registerCssFile($this->assetsUrl.'/css/'.$fileName);
  }
}
