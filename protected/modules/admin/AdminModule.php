<?php
/**
 * Admin module main file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.AdminModule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class AdminModule extends CWebModule
{
  public $name;
  public $assetsUrl;
	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'admin.models.*',
			'admin.components.*',
		));
		
		$this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);//Yii::getPathOfAlias('application.modules.admin.assets'));
    //$this->registerScriptFile('admin.js');
    //$this->registerCssFile('admin.css');

    // меняем имя сайта
    Yii::app()->name = 'HamsterCMS';

    // переопределяем страницу входа
    Yii::app()->user->loginUrl = Yii::app()->createUrl('admin/login/index');

    // устанавливаем экшен для отобраения ошибок
    Yii::app()->errorHandler->errorAction = 'admin/admin/error';
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
		
		  // this overwrites everything in the controller
      $controller->adminAssetsUrl = $this->assetsUrl;
      
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
	
	public function registerScriptFile($fileName,$position=CClientScript::POS_END)
  {
    Yii::app()->getClientScript()->registerScriptFile($this->assetsUrl.'/js/'.$fileName,$position);
  }
  
  public function registerCssFile($fileName)
  {
    Yii::app()->getClientScript()->registerCssFile($this->assetsUrl.'/css/'.$fileName);
  }
}
