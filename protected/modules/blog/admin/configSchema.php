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
      'copyright'=> array(
        'label' => 'Строка copyright (используется в RSS)',
        'type' => 'text',
        'hint' => '2012 © hamstercms.com',
      ),
    ),
    'admin' => array(
      'title' => 'Блог',
      'description' => 'Информационный контент модуль, который идеально подходит для таких рещений как: Блог, Новости и т.д.',
      'db' => array(
        'version' => '1.2.1',
        'tables' => array(
          'blog',
          'blog_tag',
          'blog_categorie',
        ),
      ),
    ),
  ),
);
