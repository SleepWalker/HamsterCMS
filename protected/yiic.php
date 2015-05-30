<?php

$composerPath = dirname(__FILE__) . '/vendor/composer/vendor';

require_once $composerPath . '/autoload.php';

// change the following paths if necessary
$yiic = $composerPath . '/yiisoft/yii/framework/yiic.php';
$config = dirname(__FILE__).'/config/console.php';

require_once($yiic);
