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
    if(isset($installType))
    {
      $this->setupDb();
      $this->setupCache();
      $this->generateConfig();
      $this->registerAdmin();
      $this->generateConfig(true);
    }else
      $this->render('index');
  }

  protected function setupDb()
  {
    if(is_array(Yii::app()->session['db']))
      return;

    // проверяем соединение
    if(isset($_POST['db']))
    {
      try{
        $dsn = "mysql:dbname={$_POST['db']['name']};host={$_POST['db']['host']}";
        $connection=new CDbConnection($dsn,$_POST['db']['user'],$_POST['db']['password']);
        $connection->active=true;

        Yii::app()->session['db'] = array(
          'connectionString' => $dsn,
          'username' => $_POST['db']['user'],
          'password' => $_POST['db']['password'],
        );
        return;
      }
      catch(CDbException $e)
      {
      }
    }

    $this->render('db', array(
      'data' => $_POST['db'],
      'error' => isset($e) ? $e->getMessage() : false,
    ));
    Yii::app()->end();
  }

  protected function setupCache()
  {
    if(is_array(Yii::app()->session['cache']))
      return;

    //TODO: Проверка соединения мемкейчед
    switch($_POST['cacheType'])
    {
    case 'filesystem':
      // кэш в файловой систем по умолчанию, потому ничего не трогаем
      return Yii::app()->session['cache'] = array();
      break;
    case 'memcached':
    case 'memcache':

      Yii::app()->session['cache'] = array(
        'class'=>'system.caching.CMemCache',
        'servers'=>array(
          array('host'=>$_POST['memcache']['host'], 'port'=>$_POST['memcache']['port'], 'weight'=>60),
        ),
      );
      if($_POST['cacheType'] == 'memcached')
        Yii::app()->session['cache']['useMemcached'] = true;
      return;
      break;
    }

    if(empty($_POST['cacheType']))
      $_POST['cacheType'] = 'filesystem';

    $this->render('cache', array(
      'data' => $_POST,
    ));
    Yii::app()->end();
  }

  protected function generateConfig($mergeFinal = false)
  {
    if(!$mergeFinal && Yii::app()->session['configUpdated'])
      return;

    $dbConfig = array(
      'components' => array(
        'cache' => Yii::app()->session['cache'],
        'db' => Yii::app()->session['db'],
      ),
    );

    // проводим финальное сливание конфигов
    // на выходе будет стартовый конфиг для нормальной работы цмс
    if($mergeFinal)
    {
      $hamsterModules = Yii::getPathOfAlias('application.config') . DIRECTORY_SEPARATOR . 'hamsterModules.php';
      if(is_file($hamsterModules))
      {
        $hamsterModules = require($hamsterModules);
      }else{
        $hamsterModules = array();
      }

      $mainConfig = require(Yii::getPathOfAlias('application.modules.admin.config') . DIRECTORY_SEPARATOR . 'main.php');
      // мерджим массивы
      $hamsterModules['config'] = CMap::mergeArray($hamsterModules['config'], $dbConfig);
      $hamster = CMap::mergeArray($mainConfig, $hamsterModules['config']); 
    }else{
      // здесь мы только подключаем бд и кэш и мерджим с конфигом install, 
      // от которого работает приложение установки
      // так как дальше нам прийдется регистрировать админа 
      $installConfig = require(Yii::getPathOfAlias('application.modules.admin.install.config') . DIRECTORY_SEPARATOR . 'install.php');
      // настраиваем менеджер авторизации, что бы на этапе регистрации
      // мы могли присвоить роль админа первому юзеру
      $dbConfig['components']['authManager'] = array(
        'class'=>'CDbAuthManager',
        'connectionID'=>'db',
        'defaultRoles' => array('guest', 'user'),
      );
      $dbConfig['components']['user'] = array(
        'class' => 'application.modules.user.components.HWebUser',
  			'allowAutoLogin'=>true,
  		);
      $dbConfig['components']['session'] = array(
        'class' => 'system.web.CDbHttpSession',
        'connectionID' => 'db',
  		);
      $hamster = CMap::mergeArray($installConfig, $dbConfig); 

      // сообщаем скрипту, что к конфигу Install уже подключена база данных 
      // и данному методу при следущем вызове можно генерировать основной конфиг сайта
      Yii::app()->session['configUpdated'] = true;
    }

    $hamster = "<?php\n\nreturn " . var_export($hamster, true) . ";";
    file_put_contents(Yii::getPathOfAlias('application.config') . '/hamster.php', $hamster);
    //FIXME: после refresh в самом конце возвращается пустая страница
    //FIXME: после refresh слетает сессия из-за того, что она становится в бд
    $this->refresh();
  }

  protected function registerAdmin()
  {
    //FIXME: не логинится юзер
    if(Yii::app()->session['adminRegistered'])
      return;

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

      if ($model->save())
      {
        $model->is_active = true; // админ у нас будет сразу активированным
        $model->save();
        $model->login();

        $authItem = new AuthItem;
        AuthItem::model()->assign($model, 'admin');

        Yii::app()->session['adminRegistered'] = true;

        Yii::app()->end();
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
}
