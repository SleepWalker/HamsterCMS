<?php
/**
 * Base class for AdminActions of Hamster CMS
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.admin.components.HAdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
/**
 * Этот класс немного расширяет стандартные экшены (CAction) Yii переопределяя их магические методы
 * таким образом, что бы создать возможность обращения к методам контроллера напрямую без использования
 * аттрибута controller класса CAction
 */

class HAdminAction extends CAction
{
  public function __get($name) 
  {
    try
    {
      return parent::__get($name);
    }catch(Exception $e){
      return $this->controller->$name;
    }
  }

  public function __set($name, $value)
  {
    try
    {
      return parent::__set($name, $value);
    }catch(Exception $e){
      return $this->controller->$name = $value;
    }
  }
  
  public function __isset($name) 
  {
    if(parent::__isset($name)) return true;
    else return $this->controller->__isset($name);
  }
  
  public function __call($name,$parameters)
  {
    try
    {
      return parent::__call($name,$parameters);
    }catch(Exception $e){
      return call_user_func_array(array($this->controller,$name),$parameters);
    }
  }
}
