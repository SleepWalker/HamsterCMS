<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class HAdminController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='/layouts/column2';
  /**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
  public $aside = array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
  public $tabs;

  // массив с информацией о модулях
  protected $_hamsterModules = array();
  
  public $actionId;
  public $actionPath;
  public $curModuleUrl; // путь к index текущего модуля, к примеру /admin/shop
  public $adminAssetsUrl;
  
  /**
   * Загружает настройки модулей Hamster
   * @return array массив с настройками
   */
  public function getHamsterModules()
  {
    $file = Yii::getPathOfAlias('application.config') . '/hamsterModules.php';
    if(!$this->_hamsterModules && file_exists($file))
      $this->_hamsterModules = require($file);
    return $this->_hamsterModules;
  }
  
  /**
   * @return array массив с информацией о модулях
   */
  public function getModulesInfo()
  {
    return  is_array($this->hamsterModules['modulesInfo']) ? $this->hamsterModules['modulesInfo'] : array();
  }
  
  /**
   * @return array массив с информацией об активных модулях
   */
  public function getEnabledModules()
  {
    return is_array($this->hamsterModules['enabledModules']) ? $this->hamsterModules['enabledModules'] : array();
  }
}
