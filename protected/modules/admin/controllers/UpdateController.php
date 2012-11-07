<?php
/**
 * UpdateController class for admin module
 *
 * Производит обновление файлов цмс
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers.UpdateController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class UpdateController extends AdminBaseController 
{ 
  public function filters()
  {
    return array(
      'accessControl',
    );
  }

  public function accessRules()
  {
    return array(
      array('allow',
      'roles'=>array('admin'),
    ),
    array('allow',
    'actions'=>array('shop', 'error', 'index', 'cart', 'blog'),
    'roles'=>array('staff'),
  ),
  array('deny',  // deny all users
  'users'=>array('*'),
),
      );
  }
  
	public function actionIndex()
	{
    $this->renderText('test');
	}
}
