<?php
return array(
  // modulewide params schema
  /*'scriptsAlias' => array(
    'label' => 'Alias папки со скриптами',
    'type' => 'text',
    'default' => 'application.mevalScripts',
  ),*/
  
  // global params schema and admin panel settings
  'hamster' => array(
    'admin' => array(
      'title' => 'Пользователи и группы',
      'description' => 'Модуль позволяет просматривать зарегистрированных пользователей системы. Управлять группами и перемещать пользователей в них. Совершать рассылки зарегистрированным или оставившим свой email пользователям.',
      'internal' => true, // этот модуль работает только внутри админки
      'db' => array(
        'version' => 1,
        'tables' => array(
          'AuthAssignment',
          'AuthItem',
          'AuthItemChild',
          'auth_user',
          'YiiSession',
        ),
      ),
    ),
  ),
);
