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
      'description' => 'Модуль, позволяющий легко расширять функционал системы, добавляя в специфические для конкретного проекта скрипты. Это могут быть разнообразные парсеры, загрузчики, скрипты обновлений базы данных, скрипты рассылок и т.д.',
      'internal' => true, // этот модуль работает только внутри админки
    ),
  ),
);
