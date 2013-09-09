<?php
return CMap::mergeArray(
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'hamster.php'),
	array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  	'params'=>array(
  		'defaultPageSize' => 20,
  	),
    'modules'=>array( 		
  		'admin',		
      'gii'=>array(
        'class'=>'system.gii.GiiModule',
        'password'=>'asd',
        // If removed, Gii defaults to localhost only. Edit carefully to taste.
        'ipFilters'=>array('127.0.0.1','::1'),
      ),
  	),
    'components'=>array(
      /*'db' => array (
        'connectionString' => 'mysql:host=localhost;dbname=DB_NAME',
        'username' => 'root',
        'password' => 'PASS',
      ),*/
      /*'mail' => array(
        'class' => 'ext.yii-mail.YiiMail',
        'transportType' => 'smtp',
        'viewPath' => 'application.views.mail',
        'transportOptions' => array(
          'host' => 'mail.ukraine.com.ua',
          'username' => 'muster@mustermann.com',
          'password' => 'pass',
          'port' => '2525',
          //'encryption' => '',
          //'timeout' => '',
          //'extensionHandlers' => '',
        ),
        'logging' => true,
        'dryRun' => false
      ),*/
    ),
  )
);
