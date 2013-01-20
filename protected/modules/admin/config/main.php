<?php
/**
 * Hamster base config file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.AdminModule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

// добавляем глобальную переменную с uri запроса
if(isset($_SERVER['REQUEST_URI']))
  $GLOBALS['_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
if(isset($_SERVER['REMOTE_ADDR']))
  $GLOBALS['_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
  
/*
* Для того, что бы было легче поддерживать актуальные конфиги во всех моих проектах
* файлы конфига разбиты на несколько частей: базовый конфиг (admin.config.main)
* и конфиг для переопределения спецефических для конкретного проекта настроек (application.config.main),
* к примеру для переопределения настроек бд, кэширования, логирования
*/

return
	array(
  	'language' => 'ru',
  	'sourceLanguage'=>'ru',
    'charset'=>'UTF-8',
    'theme' => 'default',
    
  	// preloading 'log' component
  	'preload'=>array('log'),
  
  	// autoloading model and component classes
  	'import'=>array(
  		'application.models.*',
  		'application.components.*',
  		'ext.yii-mail.YiiMailMessage',
  	),
    'onBeginRequest' => array(HBeginRequest, 'onBeginRequest'),
    //TODO: maintance mode
    /*'catchAllRequest'=>array(
        'controllerId/actionId',
            // можно передать параметры
                'param1'=>value1,
                    'param2'=>value2,
                    ),*/
  	
  	// gzip сжатие
  	/*'onBeginRequest'=>create_function('$event', 'return ob_start("ob_gzhandler");'),
    'onEndRequest'=>create_function('$event', 'return ob_end_flush();'),*/
  
  	'modules'=>array( 		
  		'admin',	
  	),
  
  	// application components
  	'components'=>array(
      'db'=>array(
        'enableParamLogging'=>true, // логирует SQL вместе с привязанными параметрами
        'charset' => 'utf8',
        'emulatePrepare' => true,
        'queryCacheID'=>'cache', // подключение кеширования запросов
        //'initSQLs'=>array("set time_zone='+00:00';"),
      ),
      'menuMap'=>array(
        'class'=>'HMenuMap', 
      ),
      'user'=>array(
  			// enable cookie-based authentication
        'class' => 'application.modules.user.components.HWebUser',
  			'allowAutoLogin'=>true,
  		),
  		'mail' => array(
   			'class' => 'ext.yii-mail.YiiMail',
   			'transportType' => 'php',
        // убрираем ошибки при отправке писем на некоторых хостах (отвечает за формат 4 параметра функции mail)
        // (по умолчанию это '-f%s', тоесть на выходе имеем '-fmailFrom@site.com')
        // на некоторых хостах пхп не может на прямую передавать параметры серверу
        'transportOptions' => false,
   			'logging' => true,
   			'dryRun' => YII_DEBUG, // when true the mail will not be sended
   		),
      'widgetFactory'=>array(
        'class'=>'EWidgetFactory',
        'widgets'=>array(
          'CPortlet'=>array(
            'titleCssClass' => 'block_title',
  		      'contentCssClass' => 'block_body',
  		      'tagName' => 'section',
  		      'htmlOptions' => array(
  		        'class' => 'block_cont',
  		      ),
          ),
          'CJuiWidget' => array(
            'themeUrl'=>'/css/jui',
          ),
        ),
      ),
      'ePdf' => array(
        'class'         => 'ext.yii-pdf.EYiiPdf',
        'params'        => array(
          'mpdf'     => array(
            'librarySourcePath' => 'application.vendors.mpdf.*',
            'constants'         => array(
              '_MPDF_TEMP_PATH' => Yii::getPathOfAlias('application.runtime'),
            ),
            'class'=>'mpdf', // the literal class filename to be loaded from the vendors folder
            'defaultParams'     => array( // More info: http://mpdf1.com/manual/index.php?tid=184
              'mode'              => '', //  This parameter specifies the mode of the new document.
              'format'            => 'A4', // format A4, A5, ...
              'default_font_size' => 0, // Sets the default document font size in points (pt)
              'default_font'      => '', // Sets the default font-family for the new document.
              'mgl'               => 10, // margin_left. Sets the page margins for the new document.
              'mgr'               => 10, // margin_right
              'mgt'               => 11, // margin_top
              'mgb'               => 11, // margin_bottom
              'mgh'               => 9, // margin_header
              'mgf'               => 9, // margin_footer
              'orientation'       => 'P', // landscape or portrait orientation
            )
          ),
        ),
      ),
      'session' => array(
        'class' => 'system.web.CDbHttpSession',
        'connectionID' => 'db',
      ),
      'cache'=>array(
        'class'=>'system.caching.CFileCache',
      ),
  		// uncomment the following to enable URLs in path-format
  		'urlManager'=>array(
  			'urlFormat'=>'path',
  			'showScriptName' => false,
  			'rules'=>array(
          // Правило url для модулей
          array(
            'class' => 'application.components.HModuleUrlRule',
          ),
  			  'site/<action:\w+>'=>'site/<action>',

  				'<controller:page|api>/<path:.*>'=>'<controller>/index',
  				
          // правило для админки action (оно же название модуля)
          'admin/<module:\w+>'=>'admin/admin/<module>',
          // правило для админки crud и subaction только create|update|delete,
          // но с возможностью использования буквенного crudid)
          'admin/<module:\w+>/<action:\w*\/?(update|delete)>/<crudid:[^\/]+>' => 'admin/admin/<module>',
          // правило для админки crud и subaction
          'admin/<module:\w+>/<action:\w*\/?\w*>/<crudid:\d+>'=>'admin/admin/<module>',
          // для всего кроме crud
          'admin/<module:\w+>/<action:([^\/]+\/?)+>'=>'admin/admin/<module>',
  			),
  		),
  		'authManager'=>array(
        'class'=>'CDbAuthManager',
        'connectionID'=>'db',
        'defaultRoles' => array('guest', 'user'),
      ),
  		
  		'errorHandler'=>array(
  	   // use 'site/error' action to display errors
          'errorAction'=>'site/error',
      ),
      
  		'log'=>array(
  			'class'=>'CLogRouter',
  			'routes'=>array(
  				array(
  					'class'=>'CFileLogRoute',
  					'levels'=>'error, warning, info',
  					'filter'=> array(
  					  'class'=>'CLogFilter',
  					  'prefixSession' => false,
              'prefixUser' => false,
              'logUser' => false,
  					  'logVars'=>array('_GET', '_POST', '_REQUEST_URI', '_REMOTE_ADDR'),
  					),
  				),
          array(
            'class'=>'CWebLogRoute',
            'enabled' => 'phpexpr:YII_DEBUG && !($_GET["ajax"] ||  $_POST["ajax"] || $_POST["ajaxSubmit"] || $_POST["ajaxIframe"])',
            'filter'=> array(
              'class'=>'CLogFilter',
              'prefixSession' => false,
              'prefixUser' => false,
              'logUser' => false,
              'logVars'=>array('_GET', '_POST', '_REQUEST_URI', '_REMOTE_ADDR'),
            ),
          ),
  				/*array(
  					'class'=>'CFileLogRoute',
  					'levels'=>'info',
  					'logFile' => 'info.log',
  				),*/
  				// uncomment the following to show log messages on web pages
  				/*
  				array(
  					'class'=>'CWebLogRoute',
  				),
  				*/
  			),
  		),
  	),
  	'defaultController' => 'page',
  );
