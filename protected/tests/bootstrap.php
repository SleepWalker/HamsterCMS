<?php
error_reporting(E_ALL);

$webRoot = explode('/protected', end($_SERVER['argv']))[0];
$protected = $webRoot . '/protected';
$composerPath = $protected . '/vendor/composer/vendor';

require_once $composerPath . '/autoload.php';

use PHPUnit\Framework\TestCase;
/**
 * CTestCase is the base class for all test case classes.
 *
 * Redeclare class to support phpunit 6+
 */
abstract class CTestCase extends TestCase
{
}

function bootstrapAliases()
{
    $modulesDir = \Yii::getPathOfAlias('application.modules');

    if (!$modulesDir) {
        throw new \DomainException('Can not locate modules directory');
    }

    \Yii::setPathOfAlias('hamster', \Yii::getPathOfAlias('application'));

    $dirs = scandir($modulesDir);

    foreach ($dirs as $dir) {
        $dirPath = \Yii::getPathOfAlias('hamster.modules.' . $dir);

        if (is_dir($dirPath)) {
            \Yii::setPathOfAlias($dir, $dirPath);
        }
    }
}

$yiit = $composerPath . '/yiisoft/yii/framework/yiit.php';

require_once($yiit);
$config = require($protected . '/config/test.php');

// require_once(dirname(__FILE__) . '/WebTestCase.php');

$_SERVER['SERVER_NAME'] = 'foo.bar';

$config['components']['assetManager'] = isset($config['components']['assetManager']) && is_array($config['components']['assetManager']) ? $config['components']['assetManager'] : [];
$config['components']['assetManager']['basePath'] = $webRoot . '/assets';

// запрещаем Yii слепо инклюдить класс расчитывая, что они находятся на пути
YiiBase::$enableIncludePath = false;

Yii::createWebApplication($config);

bootstrapAliases();
