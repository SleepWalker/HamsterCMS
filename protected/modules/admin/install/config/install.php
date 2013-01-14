<?php
return array(
  'language' => 'ru',
  'sourceLanguage'=>'ru',
  'charset'=>'UTF-8',
  'controllerMap' => array(
    'install'=>'application.modules.admin.install.controllers.InstallController',
  ),
  'import' => array (
    'application.models.*',
    'application.components.*',
  ),
  'catchAllRequest'=>array(
    'install/index',
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
    ),
  ),
);
