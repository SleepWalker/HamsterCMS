<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

/*
* Для того, что бы было легче поддерживать актуальные конфиги во всех моих проектах
* файлы конфига разбиты на несколько частей: базовый конфиг (main.php)
* и конфиг для переопределения спецефических для конкретного проекта настроек (override.php),
* к примеру для переопределения настроек бд
*/
return CMap::mergeArray(
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'hamster.php'),
	array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Pst.studio - разработка и обслуживание сайтов',
    // application-level parameters that can be accessed
  	// using Yii::app()->params['paramName']
  	'params'=>array(
  		// this is used in contact page
  		'adminEmail'=>'dev@pwn-zone.com',
  		'noReplyEmail'=>'robot@pwn-zone.com',
      'vkApiId' => '3132242',
      'RssDescription' => 'Блог Pst.studio.',
      'copyright' => '<a href="/">PST</a> © 2010',
  		'defaultPageSize' => 20,
  	),
    'modules'=>array( 		
  		'admin',		
      'blog' => array(
  		  'adminPageTitle' => 'Блог',
        'params' => array(
          'moduleName' => 'Статьи',
        ),
  		),	
      'gii'=>array(
        'class'=>'system.gii.GiiModule',
        'password'=>'asd',
        // If removed, Gii defaults to localhost only. Edit carefully to taste.
        'ipFilters'=>array('127.0.0.1','::1', '111.111.111.1'),
      ),
  	),
    'components'=>array(
      'db'=>array(
        'connectionString' => 'mysql:host=localhost;dbname=pststudio',
        'emulatePrepare' => true,
        'username' => 'root',
        'password' => 'asd5171953',
        'charset' => 'utf8',
        'enableParamLogging'=>true, // логирует SQL вместе с привязанными параметрами
      ),
      /*'mail' => array(
        'class' => 'ext.yii-mail.YiiMail',
        'transportType' => 'smtp',
        'viewPath' => 'application.views.mail',
        'transportOptions' => array(
          'host' => 'mail.ukraine.com.ua',
          'username' => 'admin@pwn-zone.com',
          'password' => '2ffe0a62',
          'port' => '2525',
          //'encryption' => '',
          //'timeout' => '',
          //'extensionHandlers' => '',
        ),
        'logging' => true,
        'dryRun' => false
      ),*/
      'cache'=>array(
        'class'=>'system.caching.CMemCache',
        'servers'=>array(
            array('host'=>'localhost', 'port'=>11211, 'weight'=>60),
        ),
        'useMemcached' => true,
      ),
    ),
  )
);