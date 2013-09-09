<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class HAdminController extends CController
{
	/**
	 * @property string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='/layouts/column2';
  /**
	 * @property array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @property array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
  public $aside = array();

  /**
   * @property array $pageActions массив с дополнительными действиями, которые появятся возле тега h1
   */
  public $pageActions;

  // массив с информацией о модулях
  protected $_hamsterModules = array();
  
  public $actionId;
  public $actionPath;     // путь к текущему действию (равен абсолютному пути из адреной строки браузера)
  public $curModuleUrl;   // путь к index текущего модуля, к примеру /admin/shop
  public $adminAssetsUrl;

  /**
   * Переопредиляем стандартный метод таким образом, что бы он искал вьюхи в следущем порядке:
   *  - Сначала во вьюхах админки (admin/views/admin/*)
   *  Далее, если активно действие администрирования модуля (AdminAction)
   *    - в темах модуля, который админиться (themes/.../moduleId/admin/*)
   *    - во вьюхах модуля, который админиться (views/moduleId/admin/*)
   *
   *  Примечание: Стандартный метод getViewFile так же будет искать вьюхи модуля admin и в темах, 
   *      но так как по умолчанию не рассчитывается, что такие будут, они не вошли в список выше
   * 
   * @param string $viewName 
   * @access public
   * @return mixed путь к файлу вьюхи или false, если файла не существует
   */
  public function getViewFile($viewName)
  {
    if(!($viewFile = parent::getViewFile($viewName)))
    {
        $basePath = Yii::app()->getViewPath();
        if ($this->action instanceof AdminAction)
        {
          // попробуем поискать в admin вьюхах текущего модуля
          // @property $this->action->id id текущего модуля, админ часть которого активна.
          $moduleViewPath = Yii::getPathOfAlias('application.modules.' . $this->action->id) . '/views';
          $viewPath = $moduleViewPath . '/admin';

          $themeBasePath = Yii::app()->getTheme()->getViewPath();
          $themeModuleViewPath = $themeBasePath . '/' . $this->action->id;
          $themeViewPath = $themeModuleViewPath . '/admin';

          if(!($viewFile = $this->resolveViewFile($viewName,$themeViewPath,$themeBasePath, $themeModuleViewPath)))
          {
            $viewFile = $this->resolveViewFile($viewName,$viewPath,$basePath, $moduleViewPath);
          }
        }
        else
        {
            // для обычных контроллеров модуля admin, в случае если в их директории нету нужной вьюхи, 
            // попробуем поискать ее во вьюхах контроллера AdminController
            $moduleViewPath = $this->module->getViewPath();
            $viewPath = $moduleViewPath . '/admin';
            $viewFile = $this->resolveViewFile($viewName,$viewPath,$basePath, $moduleViewPath);
        }
    }

    return $viewFile;
  }

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
    return  isset($this->hamsterModules['modulesInfo']) && is_array($this->hamsterModules['modulesInfo']) ? $this->hamsterModules['modulesInfo'] : array();
  }
  
  /**
   * @return array массив с информацией об активных модулях
   */
  public function getEnabledModules()
  {
    return isset($this->hamsterModules['enabledModules']) && is_array($this->hamsterModules['enabledModules']) ? $this->hamsterModules['enabledModules'] : array();
  }

  /**
   * Очищает папки assets и кэш
   * 
   * @access protected
   * @return void
   */
  protected function clearTmp()
  {
    $this->destroyDir(Yii::getPathOfAlias('webroot.assets'));
    Yii::app()->cache->flush();
  }
  
  /**
   * Полностью удаляет содержимое $dir
   * @params string $dir путь к директории
   */
  protected function destroyDir($dir) 
  {
    if(!preg_match('%/$%', $dir)) $dir .= '/';
    $mydir = opendir($dir);
    
    while(false !== ($file = readdir($mydir))) {
      if($file != "." && $file != "..") {
        //chmod($dir.$file, 0777);
        if(is_dir($dir.$file)) {
          chdir('.');
          $this->destroyDir($dir.$file.'/');
          rmdir($dir.$file) or DIE("couldn't delete $dir$file<br />");
        }
        else
          unlink($dir.$file) or DIE("couldn't delete $dir$file<br />");
      }
    }

    closedir($mydir);
  }

  /**
   * Измененный CCOntroler::renderPartial() с целью отключения jQuery при ajax запросах
   *
   * TODO: возможно разместить отключение jQuery в config (там было что-то вроде маппинга скрипта в CClientScript)
   */
  public function renderPartial($view, $data = null, $return = false, $processOutput = false)
  {
    if(isset($_POST['ajaxIframe']) || isset($_POST['ajaxSubmit']) || Yii::app()->request->isAjaxRequest)
      Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 

    return parent::renderPartial($view, $data, $return, $processOutput);
  }
  
  /**
   * Гибридный рендеринг (render/renderPartial) для форм редактирования в админке  
   * 
   * @access public
   * @return void
   */
  public function renderForm($model, $params = array())
  {
    $params = CMAp::mergeArray(array('model' => $model), $params);
        
		if(Yii::app()->request->isPostRequest)
    {
      // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
      if(!$model->hasErrors() && (property_exists($this->action, 'crud') && $this->action->crud == 'create'))
        $data = array(
          'action' => 'redirect',
          'content' => $this->curModuleUrl . 'update/'.$model->id,
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->renderPartial('update', $params, true, true),
        );
      
      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
      Yii::app()->end();
    }
    else
    {
    if(isset($_POST['ajaxIframe']) || isset($_POST['ajaxSubmit']) || Yii::app()->request->isAjaxRequest)
      $this->renderPartial('update', $params, false, true);
    else
      $this->render('update', $params);
    }
  }
}
