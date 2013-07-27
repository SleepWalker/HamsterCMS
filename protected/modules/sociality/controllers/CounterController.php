<?php
/**
 * CounterController class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sociality.controllers.CounterController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('application.modules.sociality.components.*');
class CounterController extends Controller
{  
  /**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
      'ajaxOnly + index',
		);
	}
	
  public function actionIndex($url, $sn)
  {
    $counter = $this->getShareCounter($url);

    switch(strtolower($sn))
    {
    case 'google':
      $count = $counter->getPlus1() * 1;
      break;
    default:
      $count = 0;
      break;
    }

    header('Content-type: text/json');
    echo CJSON::encode(array('count' => $count));
  }

  protected function getShareCounter($url)
  {
    $obj = new ShareCount($url);
    return $obj;
  }
}
