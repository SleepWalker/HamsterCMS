<?php
return array(  
  'codeFormat' => array(
    'label' => 'Формат кода товаров',
    'type' => 'dropdownlist',
    'items' => array(
      'normal' => 'Обычный',
      'zerofill' => 'С ведущими нулями',
      'supplierPreffix' => 'С добавлением id поставщика',
    ),
    'default' => 'normal',
    //'hint' => 'Формат задается аналогично php функции <a href="http://php.net/manual/ru/function.sscanf.php">scanf</a>.<br> Примеры: %s, %\'07s (7 знаков с ведущими нулями). Также можно добавить в начало кода id <a href="/admin/shop/suppliers">производителя</a>: <b>%\'02d</b>%\'05s. Внимание! Код производителя можно добавлять только в начале и только в указаном формате.',
  ),
  'codeLength' => array(
    'label' => 'Длина кода товаров',
    'type' => 'dropdownlist',
    'items' => array(
      '4' => 4,5,6,7,8,9,10,
    ),
    'hint' => 'Имеет значение только в случае, если используется формат кода отличный от <b>обычный</b>',
    'default' => 7,
  ),
  'viewCssFile'=> array(
    'label' => 'Адрес css файла для вида товаров',
    'type' => 'text',
  ),
  'filterAlign'=> array(
    'label' => 'Выравнивание подсказки в фильтре относительно характеристики (left|right)',
    'type' => 'dropdownlist',
    'items' => array(
      'right' => 'right',
      'left' => 'left',
    ),
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
        'version' => '1.3.1',
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
