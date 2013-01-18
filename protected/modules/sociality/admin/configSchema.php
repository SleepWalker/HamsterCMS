<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'default' => 'Социализация',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
      'vkApiId'=> array(
        'label' => 'Идентификатор API vkontakte (ApiId)',
        'type' => 'number',
      ),
    ),
    'admin' => array(
      'title' => 'Социализация',
      'description' => 'Модуль позволяющий управлять виджетами соц. сетей и комментариями на вашем сайте.',
      'db' => array(
        'version' => '1.1.1',
        'tables' => array(
          'comment',
          'comment_user',
        ),
      ),
    ),
  ),
);
