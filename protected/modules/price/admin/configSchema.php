<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'default' => 'Прайсы',
  ),
  
  // global params schema and admin panel settings
  'hamster' => array(
    'admin' => array(
      'title' => 'Прайсы',
      'description' => 'Модуль позволяющий загружать прайсы вашего магазина (в форматах *.xls, *.xlsx, *.csv), отображать их на вашем сайте с возможностью поиска и фильтрации, а так же давать возможность пользователям скачать их на компьютер.',
    ),
  ),
);
