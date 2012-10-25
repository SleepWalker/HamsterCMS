<?php
return array(
  // modulewide params schema
  'scriptsAlias' => array(
    'label' => 'Alias папки с скриптами',
    'type' => 'text',
    'defaultValue' => 'application.mevalScripts',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
    ),
    'admin' => array(
      'title' => 'Скрипты',
    ),
  ),
);