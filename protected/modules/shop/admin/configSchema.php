<?php
return array(  
  'viewCssFile'=> array(
    'label' => 'Адрес css файла для вида товаров',
    'type' => 'text',
  ),
  'filterAlign'=> array(
    'label' => 'Выравнивание подсказки в фильтре относительно характеристики (left|right)',
    'type' => 'text',
    'default' => 'right',
  ),
  'prodPageSize'=> array(
    'label' => 'Количество товаров на странице со списком товаров',
    'type' => 'number',
    'default' => 20,
  ),
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(
      // params for global section
      'currency' => array(
        'type' => 'fieldset',
        'title' => 'Настройки валюты',
        'elements' => array(
          'toDollar' => array(
            'label' => 'Курс доллара для оплаты наличными',
            'type' => 'number',
          ),
          'toEmoney' => array(
            'label' => 'Курс доллара для оплаты безналичкой',
            'type' => 'number',
          ),
        ),
      ),
    ),
    'admin' => array(
      'title' => 'Управление магазином',
      'description' => 'Модуль интернет магазина. Позволяет размещать товары с подробным описанием, фильтровать их по категориям, производителям, цене и характеристикам. Этот модуль очень тесно связан с модулем cart, который занимается обработкой заказов от клиентов.',
      'dependFrom' => array('appliaction.modules.shop'),
      'db' => array(
        'version' => 1,
        'tables' => array(
          'shop',
          'shop_brand',
          'shop_categorie',
          'shop_char',
          'shop_char_shema',
          'shop_rating',
          'shop_supplier',
        ),
      ),
    ),
  ),
);
