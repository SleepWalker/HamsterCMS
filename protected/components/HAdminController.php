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

  // массив с информацией о модулях
  protected $_hamsterModules = array();
  
  public $actionId;
  public $actionPath;
  public $curModuleUrl; // путь к index текущего модуля, к примеру /admin/shop
  public $adminAssetsUrl;

  /**
   * Возвращает массив конфигурации табов (карта действий) 
   * 
   * @access public
   * @return array
   */
  public function tabs()
  {
    return array();
  }
	
	/**
	 * Генерирует код для tabs на основе карты действий
   *
   * @return array массив для инициализации меню табов
	 */
  public function getTabs() 
  {
    if($this->action instanceof CInlineAction)
      $tabMap = $this->tabs();
    else
      $tabMap = $this->action->tabs(); // для экшенов администрации модулей

    $tabs = '';

    foreach($tabMap as $path => $name) 
    {
      if($this->action instanceof CInlineAction)
        $url = '/' . $this->module->id . '/' . $this->id . '/' . $path;
      else
        $url = '/' . $this->module->id . '/' . $this->action->id . '/' . $path;

      if($path == '') $path = 'index';

      if (is_array($name))
      {
        $hide = 0;

        switch($name['display']) 
        { // Определяем показывать ли этот таб
        case 'whenActive':
          if ($this->actionId != $path) $hide = 1;
          break;
        case 'index':
          if(!($this->actionId == 'index' || $this->actionId == 'create' || $this->actionId == 'update'))
            $hide = 1;
          break;
        default:
          if (strpos($this->actionId, $name['display']) === false)  $hide = 1;
          break;
        }
        if ($hide) continue;
        $name = $name['name'];
      }


      if ($this->actionId == $path || ($this->action->id == $path && is_a($this->action, 'CInlineAction'))) $this->pageTitle = $name;

      $tabs .= '<a href="' . $url . '">' . $name . '</a>';
    }
    return $tabs;
  }
  
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
