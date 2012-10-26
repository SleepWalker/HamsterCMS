<?php
return array(
  // modulewide params schema
  'scriptsAlias' => array(
    'label' => 'Alias папки со скриптами',
    'type' => 'text',
    'default' => 'application.mevalScripts',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
    ),
    'admin' => array(
      'title' => 'Скрипты',
      'internal' => true, // этот модуль работает только внутри админки
    ),
  ),
);