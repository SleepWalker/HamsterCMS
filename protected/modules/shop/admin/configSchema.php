<?php
return array(  
  'viewCssFile'=> array(
    'label' => 'Адрес css файла для вида товаров',
    'type' => 'text',
  ),
  'filterAlign'=> array(
    'label' => 'Выравнивание подсказки в фильтре относительно характеристики (left|right). По умолчанию right',
    'type' => 'text',
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
      'adminEmail'=> array(
        'label' => 'Емейл администратора',
        'type' => 'email',
      ),
      'noReplyEmail'=> array(
        'label' => 'Емейл робота (Например: noreply@mysite.com)',
        'type' => 'email',
      ),
      'vkApiId'=> array(
        'label' => 'Идентификатор API vkontakte (ApiId)',
        'type' => 'number',
      ),
    ),
    'admin' => array(
      'title' => 'Управление магазином',
    ),
  ),
);