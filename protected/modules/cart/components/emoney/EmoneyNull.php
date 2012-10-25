<?php
/**
 * Класс-заглушка.
 * Используется, что бы не возникали ошибки chaining методов,
 * когда функция парсинга POST запроса не может определить для какого мерчанта создавать класс
 * (к примеру если один мерчант, в отличии от другого не присылает пост запрос на successUrl)
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.components.emoney.EmoneyNull
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class EmoneyNull
{
  public function __call($name, $arguments) {
    return $this;
  }
  
  public function __set($name, $value) {
  }
}
?>