<?php
/**
 * Добавляет в модель функцию htmlEncode(), которая фильтрует 
 * все аттрибуты, переданные в массиве $attributes с помощью 
 * функции CHtml::encode()
 * 
 * @uses CActiveRecordBehavior
 * @package hamster.components.HtmlEncodeBehavior
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
class HtmlEncodeBehavior extends CActiveRecordBehavior
{
  /**
   * @property array $attributes аттрибуты, которые должны фильтроваться
   */
  public $attributes = array();


  /**
   * Кодирует все опасные аттрибуты таким образом, что бы избежать проблем с XSS и прочими атаками.
   * 
   * @access public
   * @return void
   */
  public function htmlEncode()
  {
    foreach($this->attributes as $key => $att)
      if(is_numeric($key))
        $this->owner->$att = CHtml::encode($this->owner->$att);
      else
        $this->owner->$key = CHtml::encode($att);

  }
}
