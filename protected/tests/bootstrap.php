<?php
error_reporting(E_ALL);

$webRoot = explode('/protected', end($_SERVER['argv']))[0];
$protected = $webRoot . '/protected';
$composerPath = $protected . '/vendor/composer/vendor';

require_once $composerPath . '/autoload.php';

$yiit = $composerPath . '/yiisoft/yii/framework/yiit.php';

require_once($yiit);
$config = require($protected . '/config/test.php');

// require_once(dirname(__FILE__) . '/WebTestCase.php');


$config['components']['assetManager'] = isset($config['components']['assetManager']) && is_array($config['components']['assetManager']) ? $config['components']['assetManager'] : [];
$config['components']['assetManager']['basePath'] = $webRoot . '/assets';

// запрещаем Yii слепо инклюдить класс расчитывая, что они находятся на пути
YiiBase::$enableIncludePath = false;

Yii::createWebApplication($config);
