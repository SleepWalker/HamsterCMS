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
      'description' => 'Модуль позволяющий создавать мероприятия. В нем можно указать дату, время, длительность мероприятия, написать его описание и где оно находится, как туда добраться, а так же поставить отметку места провождения на карте.',
      'db' => array(
        'version' => '1.1',
        'tables' => array(
          'event',
        ),
      ),
      'routes' => array(
        'event/index',
        'event/view',
        ),
    ),
  ),
);
