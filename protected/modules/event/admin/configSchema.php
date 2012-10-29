<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'default' => 'Мероприятия',
  ),
  'moduleUrl' => array(
    'label' => 'URI Адрес модуля',
    'type' => 'text',
    'default' => 'event',
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
      'bd' => array(
        'version' => 1,
        'tables' => array(
          'event',
        ),
      ),
    ),
  ),
);
