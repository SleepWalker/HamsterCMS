<?php
/**
 * HBeginRequest выполняет необходимые перед обработкой запроса действия
 * 
 * @package hamster.components.HBeginRequest
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
class HBeginRequest
{
  public static function onBeginRequest($event)
  {
    Hi18nBehavior::onBeginRequest($event);
  }
}
