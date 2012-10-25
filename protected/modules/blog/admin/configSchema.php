<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'defaultValue' => 'Блог',
  ),
  'moduleUrl' => array(
    'label' => 'URI Адрес модуля (Например: blog)',
    'type' => 'text',
    'defaultValue' => 'blog',
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
    ),
  ),
);