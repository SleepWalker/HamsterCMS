<?php
/**
 * Hamster base config file
 *
 * @package    hamster.modules.admin.config.main
 */

/*
 * Для того, что бы было легче поддерживать актуальные конфиги во всех моих проектах
 * файлы конфига разбиты на несколько частей: базовый конфиг (admin.config.main)
 * и конфиг для переопределения спецефических для конкретного проекта настроек (application.config.main),
 * к примеру для переопределения настроек бд, кэширования, логирования
 */

return
[
    'language' => 'ru',
    'sourceLanguage' => 'ru',
    'charset' => 'UTF-8',
    'theme' => 'default',

    'preload' => ['log', 'debug'],

    'import' => [
        'application.models.*',
        'application.components.*',
        'ext.yii-mail.YiiMailMessage',
    ],
    'onBeginRequest' => ['HBeginRequest', 'onBeginRequest'],
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

    'modules' => [
        'admin',
    ],

    // application components
    'components' => array(
        'request' => array(
            'enableCookieValidation' => true,
            //'enableCsrfValidation'=>true,
            //'csrfTokenName' => 'csrf',
        ),
        'format' => [
            'class' => 'CLocalizedFormatter',
        ],
        'db' => array(
            'enableParamLogging' => true, // логирует SQL вместе с привязанными параметрами
            'charset' => 'utf8',
            'emulatePrepare' => true,
            'tablePrefix' => '',
            'queryCacheID' => 'cache', // подключение кеширования запросов
            //'initSQLs'=>array("set time_zone='+00:00';"),
        ),
        'menuMap' => array(
            'class' => 'HMenuMap',
        ),
        'user' => array(
            // enable cookie-based authentication
            'class' => 'user\components\HWebUser',
            'allowAutoLogin' => true,
        ),
        'mail' => array(
            'class' => '\ext\hamster\Mailer',
            'mailerConfig' => array(
                'transportType' => 'php',
                // убрираем ошибки при отправке писем на некоторых хостах (отвечает за формат 4 параметра функции mail)
                // (по умолчанию это '-f%s', тоесть на выходе имеем '-fmailFrom@site.com')
                // на некоторых хостах пхп не может на прямую передавать параметры серверу
                'transportOptions' => false,
                'logging' => true,
                'dryRun' => 'phpexpr:YII_DEBUG', // when true the mail will not be sended
            ),
        ),
        'widgetFactory' => array(
            'class' => 'EWidgetFactory',
            'widgets' => array(
                'CPortlet' => array(
                    'titleCssClass' => 'block_title',
                    'contentCssClass' => 'block_body',
                    'tagName' => 'section',
                    'htmlOptions' => array(
                        'class' => 'block_cont',
                    ),
                ),
                'CJuiWidget' => array(
                    'themeUrl' => '/css/jui',
                ),
            ),
        ),
        'openGraph' => [
            'class' => '\hamster\components\OpenGraph',
        ],
        'ePdf' => array(
            'class' => 'ext.yii-pdf.EYiiPdf',
            'params' => array(
                'mpdf' => array(
                    'librarySourcePath' => 'application.vendor.composer.vendor.mpdf.mpdf.*',
                    'constants' => array(
                        '_MPDF_TEMP_PATH' => Yii::getPathOfAlias('application.runtime'),
                    ),
                    'class' => 'mpdf', // the literal class filename to be loaded from the vendors folder
                    'defaultParams' => array(// More info: http://mpdf1.com/manual/index.php?tid=184
                        'mode' => '', //  This parameter specifies the mode of the new document.
                        'format' => 'A4', // format A4, A5, ...
                        'default_font_size' => 0, // Sets the default document font size in points (pt)
                        'default_font' => '', // Sets the default font-family for the new document.
                        'mgl' => 10, // margin_left. Sets the page margins for the new document.
                        'mgr' => 10, // margin_right
                        'mgt' => 11, // margin_top
                        'mgb' => 11, // margin_bottom
                        'mgh' => 9, // margin_header
                        'mgf' => 9, // margin_footer
                        'orientation' => 'P', // landscape or portrait orientation
                    ),
                ),
            ),
        ),
        'session' => array(
            'class' => 'system.web.CDbHttpSession',
            'connectionID' => 'db',
            'autoCreateSessionTable' => 'phpexpr:YII_DEBUG',
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache',
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => [
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => [
                // Правило url для модулей
                [
                    'class' => '\application\components\HModuleUrlRule',
                ],
                'site/<action:\w+>' => 'site/<action>',

                '<controller:api>/<path:.*>' => '<controller>/index',

                // default rules for Yii controllers
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',

                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
            ],
        ],

        // замена стандартному ClientScript, которая умеет не отправлять скрипты при аякс запросах. пока что для теста работает только на бекенде
        'clientScript' => [
            'class' => '\nlac\NLSClientScript',
            //'excludePattern' => '/\.tpl/i', //js regexp, files with matching paths won't be filtered is set to other than 'null'
            //'includePattern' => '/\.php/', //js regexp, only files with matching paths will be filtered if set to other than 'null'

            'packages' => [
                'js-logger' => [
                    'basePath' => 'hamster.modules.admin.assets.js.logger',
                    'js' => ['logger.js'],
                ],
            ],

            'mergeJs' => false, //def:true
            // 'compressMergedJs' => false, //def:false

            'mergeCss' => false, //def:true
            // 'compressMergedCss' => false, //def:false

            // 'mergeJsExcludePattern' => '/edit_area/', //won't merge js files with matching names

            // 'mergeIfXhr' => true, //def:false, if true->attempts to merge the js files even if the request was xhr (if all other merging conditions are satisfied)

            // 'serverBaseUrl' => 'http://localhost', //can be optionally set here
            // 'mergeAbove' => 1, //def:1, only "more than this value" files will be merged,
            // 'curlTimeOut' => 10, //def:10, see curl_setopt() doc
            // 'curlConnectionTimeOut' => 10, //def:10, see curl_setopt() doc

            // 'appVersion'=>1.0 //if set, it will be appended to the urls of the merged scripts/css
        ],

        'authManager' => [
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
            'defaultRoles' => ['guest', 'user'],
        ],

        'errorHandler' => [
            // use 'site/error' action to display errors
            'errorAction' => YII_DEBUG ? null : 'site/error',
        ],

        'log' => [
            'class' => 'CLogRouter',
            'routes' => [
               [
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                    'filter' => [
                        'class' => 'hamster\components\LogContext',
                        'logVars' => [['_SERVER', 'REMOTE_ADDR'], ['_SERVER', 'HTTP_USER_AGENT'], '_GET', '_POST'],
                    ],
                ],
            ],
        ],
        'debug' => [
            'class' => 'application.vendor.composer.vendor.zhuravljov.yii2-debug.Yii2Debug',
            'enabled' => 'phpexpr:YII_DEBUG && isset($_SERVER["SERVER_NAME"])',
        ],
    ),
    'defaultController' => 'page/page',
];
