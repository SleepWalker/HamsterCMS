<?php

class InstallController extends Controller
{
  /**
   * @property CWebModule $_AdminModule моудль админки
   */
  protected $_adminModule;

  /**
   * @property string $adminAssetsUrl
   */
  public $adminAssetsUrl;

  public function init()
  {
    $s = DIRECTORY_SEPARATOR;
    // Создаем модуль админки
    // $this->_adminModule = Yii::createComponent('application.modules.admin.AdminModule', 'install', null);

    $this->adminAssetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.admin.assets'),-1,YII_DEBUG);
    $requirmentsAssets = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.admin.install.requirements.css'),-1,YII_DEBUG);
 
    Yii::app()->getClientScript()->registerCssFile($requirmentsAssets.'/main.css');
    Yii::app()->setViewPath(Yii::app()->basePath."{$s}modules{$s}admin{$s}install{$s}views");
    Yii::app()->clientScript->registerCoreScript('jquery');
    $this->layout = "//layouts/main";
  }

  public function actionIndex()
  {
    //TODO: проверка наличия модуля админа и необходимых конфигов и дампов бд + HWebUser
    //TODO: проверка прав на запись
    //TODO: создание необходимых таблиц типа auth_user, authitem и т.д.
    $installType = isset($_POST['installType']) ? $_POST['installType'] : Yii::app()->session['installType'];
    if(isset($_POST['installType']))
      Yii::app()->session['installType'] = $_POST['installType'];
      
    if(isset($installType) && 0)
    {
      $this->setupDb();
      $this->setupCache();
      $this->generateConfig();
      $this->registerAdmin();
      $this->generateConfig(true);
    }
    $this->render('index');
  }

  public function actionDb()
  {

    // проверяем соединение
    if(isset($_POST['db']['host']))
    {
      try
      {
        $dsn = "mysql:dbname={$_POST['db']['name']};host={$_POST['db']['host']}";
        $connection=new CDbConnection($dsn,$_POST['db']['username'],$_POST['db']['password']);
        $connection->active=true;
        
        Yii::app()->session['db'] = $_POST['db'];
        $this->redirect('/cache');
      }
      catch(CDbException $e)
      {}
    }
    elseif(empty($_POST['db']))
        $_POST['db'] = Yii::app()->session['db'];

    $this->render('db', array(
      'data' => $_POST['db'],
      'error' => isset($e) ? $e->getMessage() : false,
    ));
  }

  public function actionCache()
  {
    //TODO: Проверка соединения мемкейчед
    if(isset($_POST['cacheType']))
    {
      Yii::app()->session['cache'] = $_POST;
      $this->redirect('/validate');
    }
    else
    {
      $_POST['cacheType'] = 'filesystem';
      if(!empty(Yii::app()->session['cacheType']))
        $_POST['cacheType'] = Yii::app()->session['cacheType'];
    }

    $this->render('cache', array(
      'data' => $_POST,
    ));
  }
  
  public function actionValidate()
  {
  
    if($this->validateConfig())
    {
      // генерируем временный конфиг для регистрации юзера администратора
      $this->generateConfig();
      $this->redirect('/register');
    }
    else
    {
      throw new CException('При проверке конфигурационных данных возникла не известная ошибка! :(');
    }
  }

  /**
   * Регистрация администратора
   */
  public function actionRegister()
  {
    //TODO: сделать логин при редиректе, что бы сессия таки переключилась на дб и юзер залогинился
    if(!$this->validateConfig())
      return;
    if(Yii::app()->session['adminRegistered'])
    {
      $this->generateConfig(true);
      $this->redirect('/admin');
    }
      
    // на этом этапе нам уже необходимо создать первые таблицы в бд
    $this->restoreDb();

    $model = new User('register');

    // AJAX валидация
    if(isset($_POST['ajax']))
    {
      $model->attributes = $_POST['User'];
      echo CActiveForm::validate($model);
      Yii::app()->end();
    }

    if(isset($_POST['User']))
    {
      $model->attributes = $_POST['User'];

      if ($model->validate())
      {
        $model->is_active = true; // админ у нас будет сразу активированным
        $model->save();

        $authItem = new AuthItem;
        AuthItem::model()->assign($model, 'admin');
        
        $model->login();

        Yii::app()->session['adminRegistered'] = true;
        
        // генерируем финальный конфиг
        $this->generateConfig(true);
        $this->redirect('/admin');
      }
    }
	  
    $this->pageTitle = 'Регистрация пользователя администратора';
	  $form = $this->renderPartial('application.views.site.register', array(
      'model' => $model,	
    ), true, true);

    $this->render('admin', array(
      'form' => $form,
    ));
    Yii::app()->end();
  }

  protected function generateConfig($mergeFinal = false)
  {
    $config = $this->getValidatedConfig();

    // проводим финальное сливание конфигов
    // на выходе будет стартовый конфиг для нормальной работы цмс
    if($mergeFinal)
    {
      // TODO: совершить поиск установленных в системе модулей и произвести обновление конфига-информации о модулях (hamsterModules.php)
      $hamsterModules = Yii::getPathOfAlias('application.config') . DIRECTORY_SEPARATOR . 'hamsterModules.php';
      if(is_file($hamsterModules))
      {
        $hamsterModules = require($hamsterModules);
      }else{
        $hamsterModules = array('config' => array());
      }

      // мерджим массивы
      $mainConfig = require(Yii::getPathOfAlias('application.modules.admin.config') . DIRECTORY_SEPARATOR . 'main.php');
      // мерджим массивы
      $hamsterModules['config'] = CMap::mergeArray($hamsterModules['config'], $config);
      $hamster = CMap::mergeArray($mainConfig, $hamsterModules['config']); 

      $hamsterModules = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";
      file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $hamsterModules);
    }else{
      // здесь мы только подключаем бд и кэш и мерджим с конфигом install, 
      // от которого работает приложение установки
      // так как дальше нам прийдется регистрировать админа 
      $installConfig = require(Yii::getPathOfAlias('application.modules.admin.install.config') . DIRECTORY_SEPARATOR . 'install.php');
      // настраиваем менеджер авторизации, что бы на этапе регистрации
      // мы могли присвоить роль админа первому юзеру
      $config['components']['authManager'] = array(
        'class'=>'CDbAuthManager',
        'connectionID'=>'db',
        'defaultRoles' => array('guest', 'user'),
      );
      $config['components']['user'] = array(
        'class' => 'application.modules.user.components.HWebUser',
  			'allowAutoLogin'=>true,
  		);
  		/*
      $config['components']['session'] = array(
        'class' => 'system.web.CDbHttpSession',
        'connectionID' => 'db',
  		);
  		*/
      $hamster = CMap::mergeArray($installConfig, $config); 
    }

    $hamster = "<?php\n\nreturn " . var_export($hamster, true) . ";";
    file_put_contents(Yii::getPathOfAlias('application.config') . '/hamster.php', $hamster);
  }
  
  protected function getValidatedConfig()
  {
    if(!$this->validateConfig())
      return null;
      
    $configParams['db'] = Yii::app()->session['db'];
    
    $config['components']['db'] = array (
      'charset' => 'utf8',
      'emulatePrepare' => true,
      'connectionString' => "mysql:dbname={$configParams['db']['name']};host={$configParams['db']['host']}",
      'username' => $configParams['db']['username'],
      'password' => $configParams['db']['password'],
    );
    
    $configParams['cache'] = Yii::app()->session['cache'];  
     
    if($configParams['cache']['cacheType'] == 'filesystem')
      // кэш в файловой систем по умолчанию, потому ничего не трогаем
      $config['components']['cache'] = array('class'=>'system.caching.CFileCache');
    else 
    {
      $config['components']['cache'] = array(
          'class'=>'system.caching.CMemCache',
          'servers'=>array(
              array('host'=>$configParams['cache']['memcache']['host'], 'port'=>$configParams['cache']['memcache']['port'], 'weight'=>60),
          ),
      );
      
      if($configParams['cache']['cacheType'] == 'memcached')
        $config['components']['cache']['useMemcached'] = true;
    }
    
    return $config;
  }
  
  protected function validateConfig()
  {
    if(empty(Yii::app()->session['db']))
        $this->redirect('/db');
    if(empty(Yii::app()->session['cache']))
        $this->redirect('/cache');
        
    return true;
  }
  
  protected function restoreDb()
  {
		if(Yii::app()->db->getSchema()->getTable('auth_user') === null)
		{
		  $sql = file_get_contents(Yii::getPathOfAlias('application.modules.user.admin').DIRECTORY_SEPARATOR.'schema.mysql.sql');
		  Yii::app()->db->createCommand($sql)->execute();
		  $sql = file_get_contents(Yii::getPathOfAlias('application.controllers.page').DIRECTORY_SEPARATOR.'schema.mysql.sql');
		  Yii::app()->db->createCommand($sql)->execute();
		}
  }
}
