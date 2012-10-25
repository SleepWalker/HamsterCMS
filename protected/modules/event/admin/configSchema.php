<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'defaultValue' => 'Мероприятия',
  ),
  'moduleUrl' => array(
    'label' => 'URI Адрес модуля (Например: blog)',
    'type' => 'text',
    'defaultValue' => 'event',
  ),
  'yandexApiKey' => array(
    'label' => '<a href="http://api.yandex.ru/maps/form.xml">Ключ API</a> для Яндекс Карт',
    'type' => 'text',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
    ),
    'admin' => array(
      'title' => 'Мероприятия',
    ),
  ),
);