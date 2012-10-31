<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'default' => 'Блог',
  ),
  'moduleUrl' => array(
    'label' => 'URI Адрес модуля',
    'type' => 'text',
    'default' => 'blog',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
      'RssDescription'=> array(
        'label' => 'Описание сайта в RSS',
        'type' => 'text',
      ),
      'vkApiId'=> array(
        'label' => 'Идентификатор API vkontakte (ApiId)',
        'type' => 'number',
      ),
    ),
    'admin' => array(
      'title' => 'Блог',
      'description' => 'Информационный контент модуль, который идеально подходит для таких рещений как: Блог, Новости и т.д.',
      'bd' => array(
        'version' => 1,
        'tables' => array(
          'blog',
          'blog_tag',
        ),
      ),
    ),
  ),
);
