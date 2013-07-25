<?php
return array(
  'language' => 'ru',
  'sourceLanguage'=>'ru',
  'charset'=>'UTF-8',
  'defaultController' => 'install',
  'controllerMap' => array(
    'install'=>'application.modules.admin.install.controllers.InstallController',
  ),
  'import' => array (
    'application.models.*',
    'application.components.*',
  ),
  'components' => array(
    'modules'=>array( 		
      'install',	
    ),
    'session' => array(
      'class' => 'system.web.CHttpSession',
    ),
    'urlManager'=>array(
      'urlFormat'=>'path',
      'showScriptName' => false,
      'rules'=>array(
        '' => 'install/index',
        '<route:.*>' => 'install/<route>',
      ),
    ),
  ),
);
