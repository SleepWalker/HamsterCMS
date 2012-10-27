<?php
/**
 * AdminController class for admin module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers.AdminController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class AdminController extends Controller
{ 
  // массив с информацией о модулях
  protected $_hamsterModules = array();
  
  public $actionId;
  public $actionPath;
  public $curModuleUrl; // путь к index текущего модуля, к примеру /admin/shop
  public $tabs;
  public $aside = array();
  public $adminAssetsUrl;
  
  /**
	 * @return массив действий контроллера
	 */
	public function actions()
  {
    $enabledModules = $this->enabledModules;
    $enabledModules['page'] = 'application.controllers.page.AdminAction';
    return $enabledModules;
  }
  
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
            'actions'=>array('shop', 'index', 'cart', 'blog'),
            'roles'=>array('staff'),
          ),
          array('deny',  // deny all users
    				'users'=>array('*'),
    			),
      );
  }
  //expression: specifies a PHP expression whose value indicates whether this rule matches. In the expression, you can use variable $user which refers to Yii::app()->user.
  
	public function actionIndex()
	{
		$this->render('index');
		/*$auth=Yii::app()->authManager;
 
    $auth->createOperation('createPost','create a post');
    $auth->createOperation('readPost','read a post');
    $auth->createOperation('updatePost','update a post');
    $auth->createOperation('deletePost','delete a post');
     
    $bizRule='return Yii::app()->user->id==$params["post"]->authID;';
    $task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
    $task->addChild('updatePost');
     
    $role=$auth->createRole('reader');
    $role->addChild('readPost');
     
    $role=$auth->createRole('author');
    $role->addChild('reader');
    $role->addChild('createPost');
    $role->addChild('updateOwnPost');
     
    $role=$auth->createRole('editor');
    $role->addChild('reader');
    $role->addChild('updatePost');
     
    $role=$auth->createRole('admin');
    $role->addChild('editor');
    $role->addChild('author');
    $role->addChild('deletePost');
     
    $auth->assign('reader','readerA');
    $auth->assign('author','authorB');
    $auth->assign('editor','editorC');
    $auth->assign('admin','adminD');*/
    
    /*$auth=Yii::app()->authManager;
    $role=$auth->createRole('admin','Super User');
    $role=$auth->createRole('stuff', 'Managers of Shop');
    $auth->assign('admin',1);
    $auth->assign('admin',9);*/
	}
	
	public function actionLogs()
	{
	  //http://www.yiiframework.com/wiki/232/using-filters-with-cgridview-and-carraydataprovider/
	  $logString = file_get_contents('protected/runtime/application.log');
	  // добавляем разделитель, по которому будем делить строку
	  $logString = preg_replace('/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/m', '--Separator--$0', $logString);
    // Добавляем еще один сепаратор, что бы отображалась и последняя запись в логе
    $logString .= '--Separator--';
    preg_match_all('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[([^\]]+)\] \[([^\]]+)\] (.*?)--Separator--/s', $logString, $matches, PREG_SET_ORDER);
    $matches = array_reverse($matches);
	  $dataProvider=new CArrayDataProvider($matches, array(
        'id'=>'log',
        'pagination'=>array(
            'pageSize'=>20,
        ),
    ));

	  $this->render('log', array(
	    'dataProvider' => $dataProvider,
	  ));
	}
	
	public function actionBackup()
	{
	  Yii::import('admin.extensions.yii-database-dumper.SDatabaseDumper');
    
    if(!is_dir(Yii::getPathOfAlias('application.runtime.backup')))
      mkdir(Yii::getPathOfAlias('application.runtime.backup')); // создаем директорию для дампов
      
    $filePath = Yii::getPathOfAlias('application.runtime.backup').DIRECTORY_SEPARATOR;
      
    // Восстановление из бекапа
    if($_GET['restore'])
    {
      $sqlFile = $filePath.$_GET['restore'];
      if(file_exists($sqlFile))
      {
        $sql = file_get_contents($sqlFile);
        if(strpos($sqlFile, 'gz'))
        {
          $sql=gzinflate(substr($sql,10,-8));;
        }
        // чистим бд
        $dumper = new SDatabaseDumper;
        $dumper->flushDb();
        
        // запускаем sql комманды
        $db = Yii::app()->db;
        $command=$db->createCommand($sql);
        $rowCount=$command->execute();
        
        Yii::app()->user->setFlash('success','База успешно восстановлена. Затронуто строк: '.$rowCount);
      }
      // T!: сделать отправку на восстановление из бекапа через пост
      $this->redirect(array('/admin/backup'));
    }
    
    // удаление бекапа
    if($_GET['delete'])
    {
      if(file_exists($filePath.$_GET['delete']))
      {
        if(unlink($filePath.$_GET['delete']) === true)
          Yii::app()->user->setFlash('success','Бекап ' . $_GET['delete'] . ' успешно удален');
      }
      $this->redirect(array('/admin/backup'));
    }
    
    if(Yii::app()->request->isPostRequest)
    {
      if($_POST['flushDb'])
      {
        $dumper = new SDatabaseDumper;
        if($dumper->flushDb())
          Yii::app()->user->setFlash('success','База успешно очищена');
      }else{
        $dumper = new SDatabaseDumper;
        // Get path to backup file
        $file = $filePath . 'dump_'.date('Y-m-d_H_i_s').'.sql';
        
        $dump = $dumper->getDump();
        // Gzip dump
        if(function_exists('gzencode'))
            file_put_contents($file.'.gz', gzencode($dump));
        else
            file_put_contents($file, $dump);
      }
      $this->refresh();
    }
	  
	  // список файлов в директории
	  $fileListOfDirectory = array();
    $pathTofileListDirectory = Yii::getPathOfAlias('application.runtime.backup');
    foreach( new DirectoryIterator($pathTofileListDirectory) as $file) {
        if( $file->isFile() === TRUE) {
            array_push($fileListOfDirectory, array(
              'name' => $file->getBasename(),
              'size' => $file->getSize(),
              'time' => $file->getMTime()
            ));
        }
    }
    
    $dataProvider=new CArrayDataProvider($fileListOfDirectory, array(
      'pagination'=>array(
          'pageSize'=>20,
      ),
      'sort'=>array(
        'attributes'=>array(
          'time',
        ),
        'defaultOrder'=>array(
          'time'=>CSort::SORT_DESC,
        )
      ),
    ));
    
    $this->render('backup', array(
      'dataProvider'=>$dataProvider,
    ));
	}
	
	/**
	 * Генерирует код для tabs на основе карты действий
	 */
  public function getTabs() 
  {
    $tabMap = $this->action->tabs();

    foreach($tabMap as $path => $name) 
    {
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

      
      if ($this->actionId == $path) $this->pageTitle = $name;
      
      $tabs .= '<a href="' . $url . '">' . $name . '</a>';
    }
    return $tabs;
  }
  
  /**
	 * Выбирает какое действие администрирования запустить
	 */
  protected function afterAction(CAction $action) 
  {
    //if ($action->id == 'index' || $action->id == 'logs') return true;
    
    $path = implode('/', array(
      $_GET['module'],
      $_GET['action'],
    ));
    $path = preg_replace("/^\//", "", $path); // убираем "/" в начале строки
    $path = preg_replace("/\/$/", "", $path); // убираем "/" в конце строки
    $path = preg_replace("/\/\//", "/", $path); // убираем "//"
    
    if(Yii::app()->request->isPostRequest && isset($_POST['ajaxAction'])) $_GET['ajaxAction'] = $_POST['ajaxAction']; 
    
    if(isset($_GET['ajaxAction'])) 
      $path .= '/'.$_GET['ajaxAction'];
    
    if(!empty($path)) // @actionId нормализирование название действия
    {
      $actionParts = array_map('ucfirst', explode('/', $path));
      $actionId = implode($actionParts);
    }
    else
      $actionId = 'Index';
    
    while( !method_exists($this->action, 'action' . $actionId) )
    {
      if(method_exists($this, 'action' . $actionId)) return true;
      if(count($actionParts) == 0) // варианты действий закончились - возвращаем ошибку
        throw new CHttpException(404,'Запрашиваемая страница не существует.');
      unset($actionParts[count($actionParts)-1]);
      $actionId = implode($actionParts);
    }
    
    /*if(($ca=$this->createController($route))!==null)
  {
    list($controller,$actionID)=$ca;
    $oldController=$this->_controller;
    $this->_controller=$controller;
    $controller->init();
    $controller->run($actionID);
    $this->_controller=$oldController;
  }
  
  new $className($controllerID.$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
  */
    
    $this->actionId = ($actionId == 'Index')?'index':implode('/', array_map('strtolower', $actionParts) );
    $this->curModuleUrl = '/' . $this->module->id . '/' . $this->action->id . '/';
    $this->actionPath = $this->curModuleUrl . ( ($this->actionId == 'index')?'':$this->actionId . '/'); // admin/{имя модуля, что администрируется}/{имя действия администрирования}

    
    
    /*if($_GET['module'] == 'photo')
    {
      Yii::import($_GET['module'] . '.admin.adminController', true);
      $controller = new AdminContro
    }
    else
    {
      $this->tabs = $this->getTabs();
      return call_user_func( array($this->action, 'action' . $actionId) );
    }*/
    
    $this->tabs = $this->getTabs();
    return call_user_func( array($this->action, 'action' . $actionId) );
    
    return true;
  }
  
  /**
	 * Возвращает id редактируемого материала
	 */
  public function getCrudid()
  {
    if (empty($_GET['crudid'])) return null;
    return (int)$_GET['crudid'];
  }
  
  /**
	 * Возвращает id редактируемого материала
	 */
  public function getCrud()
  {
    $action = $_GET['action'];
    if (strpos($action, 'create') !== false) $crud = 'create';
    if (strpos($action, 'update') !== false) $crud = 'update';
    if (strpos($action, 'delete') !== false) $crud = 'delete';
    else $crud = array_pop(explode('/' , $action));
    
    return $crud;
  }
    
  /**
	 * Конвертируем переменную $_GET['path'] в массив
	 * Запоминаем колличество итемов на странице для пагинатора
	 */
  /*protected function beforeAction(CAction $action) 
  {
    
    $_GET['path'] = $path;
    
    if (isset($_GET['pageSize'])) 
    {
        Yii::app()->user->setState('pageSize',(int)$_GET['pageSize']);
        unset($_GET['pageSize']);
    }
    
    return true;
  }*/
  
  /*public function createUrl($route,$params=array(),$ampersand='&')
  {
          
          //THIS HACK IS MADE BY paulusmikkola@hotmail.com. Yii seems to have module conflict with CGridview and UrlManager
          $newParams = array();
          foreach($params as $key => $p)
          {
                  //insert your own controller + /admin instead of "person/admin" and "invoice/admin"
                 // if(substr($key, 0, 12) != "person/admin" && substr($key, 0, 13) != "invoice/admin")
                  //{
                          $newParams[$key] = $p;
                  //}               
          }
          $params = $newParams;
          //END OF HACK
          //print_r($params);
          return Yii::app()->createUrl($route,$params,$ampersand);
  }*/
  
  /**
   *  Метод для загрузки изображений через redactorJS
   *
   *  @source http://redactorjs.com/docs/images/
   */
  public function actionImageUpload()
  {
    Image::turnOffWebLog(); // отключили weblog route
    
    $image=new Image;
    
    $image->uImage=CUploadedFile::getInstanceByName('file');
    
    if($image->save())
    {
      echo $image->getHtml();
      Yii::app()->end();
    }
 
    throw new CHttpException(403,'The server is crying in pain as you try to upload bad stuff');
  }
  
  /**
   *  @return JSON массив с информацией о загруженных изоображениях для redactorJS
   */
  public function actionUploadedImages()
  {
    Image::turnOffWebLog(); // отключили weblog route
    
    $images = Image::model()->findAll();
 
    foreach($images as $image)
      $jsonArray[]=array(
        'thumb' => $image->thumb,
        'image' => $image->normal,
        'full' => $image->full,
      );
 
    header('Content-type: application/json');
    echo CJSON::encode($jsonArray);
    
    /*$images = array();
    
    $dir = Yii::getPathOfAlias('webroot') . '/uploads/imageuploads/';
    
    $handler = opendir($dir);
 
    while ($file = readdir($handler))
    {
      if ($file != "." && $file != "..")
        $images[] = $file;
    }
    closedir($handler);
 
    $jsonArray=array();
 
    foreach($images as $image)
      $jsonArray[]=array(
        'thumb'=>Yii::app()->baseUrl.'/uploads/imageuploads/'.$image,
        'image'=>Yii::app()->baseUrl.'/uploads/imageuploads/'.$image
      );
 
    header('Content-type: application/json');
    echo CJSON::encode($jsonArray);*/
  }
  
  /**
   *  Действие, для управления настройками модулей
   */
  public function actionConfig()
  {
    $this->pageTitle = 'Настройки Hamster';
    $modulesMenu['/admin/config'] = 'Основные настройки';
    foreach($this->modulesInfo as $moduleId => $moduleInfo)
    {
      $isEnabled = isset($this->enabledModules[$moduleId]);
      $onoffLabel = array('switchOff', 'switchOn');
      $modulesMenu['/admin/config?m='.$moduleId] = '<b onclick="location.href=\'/admin/switchmodule?m='.$moduleId.'\'; return false;" class="' . $onoffLabel[$isEnabled] . '"></b> ' . $moduleInfo['title'];
    }
    $this->aside['Доступные модули<a href="/admin/modulediscover" class="icon_refresh"></a>'] = $modulesMenu;

    if($_GET['m'])
    {
      $config = Config::load($_GET['m']);
      if($config)
      {
        if(Yii::app()->request->isPostRequest)
        {
          if($config->CForm->submitted('submit') && $config->CForm->validate())
          {
            if(!$config->save(false))
              throw new CHttpException(403,'При сохранении конфигурации возникли ошибки');
            $this->refresh();
          }
        }
        $this->pageTitle = $config->adminTitle;
        if($config->CForm)
        {
          echo $this->render('CFormUpdate', array(
            'form' => $config->CForm,
          ));
        }else{
          $this->renderText('У этого модуля нету настроек для редактирования');
        }
      }
    }else{
      $this->renderText('tmp');
    }
  }
  
  /**
   * Действие, определяющее наличие модулей в системе
   */
  public function actionModuleDiscover()
  {
    $path = Yii::getPathOfAlias('application.modules');
    $dirs = scandir($path); 
    $modulesInfo = $this->modulesInfo;
    
    foreach($dirs as $moduleName)
    {
      if(in_array($moduleName, array('.', '..'))) continue;
      if(is_dir($path.'/'.$moduleName))
      {
        $modulePath = Yii::getPathOfAlias('application.modules.' . $moduleName);
        if(is_dir($modulePath . '/admin'))
        {
          $config = Config::load($moduleName);
          if($modulesInfo[$moduleName]['title'] == '')
            $modulesInfo[$moduleName]['title'] = $config->defAdminTitle;
          
          if(file_exists($modulePath.'/admin/AdminAction.php'))
            $adminActions[$moduleName] = 'application.modules.' . $moduleName . '.admin.AdminAction';
        }
      }
    }
    
    $hamsterModules = $this->hamsterModules;
    $hamsterModules['modulesInfo'] = $modulesInfo;
    
    $hamsterModules = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";
    
    file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $hamsterModules);
    
    // Обновим статус модуля в конфиге (T!: честно говоря грубый способ... но пока так)
    Config::load($moduleName)->save(false);
    
    // в добавок мы еще почистим assets
    $this->destroyDir(Yii::getPathOfAlias('webroot.assets'));
      
    $this->redirect('/admin/config');
  }
  
  /**
   * Полностью удаляет содержимое $dir
   * @params string $dir путь к директории
   */
  function destroyDir($dir) 
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
   * Включает или выключает модуль, переданный в $_GET['m'], после чего редиректит на /admin/config?m=...
   */
  public function actionSwitchModule()
  {
    $enabledModules = $this->enabledModules;
    $moduleName = $_GET['m'];
    if($moduleName)
    {
      $moduleAdminPath = Yii::getPathOfAlias('application.modules.' . $moduleName . '.admin');
      if(array_key_exists($moduleName, $enabledModules))
      {
        unset($enabledModules[$moduleName]);
      }else{
        if(file_exists($moduleAdminPath.'/AdminAction.php'))
          $enabledModules[$moduleName] = 'application.modules.' . $moduleName . '.admin.AdminAction';
        $redirectParams = '?m=' . $moduleName;
      }
      
      $hamsterModules = $this->hamsterModules;
      $hamsterModules['enabledModules'] = $enabledModules;
          
      $configStr = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";
      file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $configStr);
      
      // Обновим статус модуля в конфиге (T!: честно говоря грубый способ... но пока так)
      Config::load($moduleName)->save(false);
      
      $this->redirect('/admin/config' . $redirectParams);
    }
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
