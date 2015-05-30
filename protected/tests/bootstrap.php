<?php
error_reporting(E_ALL);

$composerPath = dirname(__FILE__) . '/../vendor/composer/vendor';

require_once $composerPath . '/autoload.php';

$yiit = $composerPath.'/yiisoft/yii/framework/yiit.php';
$config = dirname(__FILE__) . '/../config/test.php';

require_once($yiit);
// require_once(dirname(__FILE__) . '/WebTestCase.php');

// запрещаем Yii слепо инклюдить класс расчитывая, что они находятся на пути
YiiBase::$enableIncludePath = false;

Yii::createWebApplication($config);
