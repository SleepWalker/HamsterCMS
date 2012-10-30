<?php
/**
 * Cart module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.CartModule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class CartModule extends CWebModule
{
  public $adminPageTitle;
  public $assetsUrl;
	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
    $this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    
		// import the module-level models and components
		$this->setImport(array(
			'cart.models.*',
			'cart.components.*',
			'shop.models.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
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
