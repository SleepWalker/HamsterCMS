<?php
// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

error_reporting(E_ALL); // ^ E_NOTICE);
ini_set('display_errors', YII_DEBUG * 1);

require dirname(__FILE__) . '/protected/vendor/composer/vendor/autoload.php';

$config = dirname(__FILE__) . '/protected/config/main.php';

Yii::createWebApplication($config)->run();
