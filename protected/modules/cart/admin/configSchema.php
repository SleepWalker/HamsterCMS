<?php
return array(
  'bccEmails'=> array(
    'label' => 'Email адреса менеджеров, которым будут отправляться скрытые копии писем с информацией о заказе (через запятую)',
    'type' => 'text',
  ),
  'deliveryTypes' => array(
    'type' => 'checkboxlist',
    'label' => 'Способы доставки',
    'items' => array(
      1 => 'Доставка курьером по Киеву',
      2 => 'Самовывоз',
      3 => 'Доставка службой "Нова пошта" по Украине',
    ),
  ),
  'emoney' => array(
    'type' => 'fieldset',
    'title' => 'Настройки WebMoney',
    'elements' => array(
      //====== WEBMONEY
      'WM' => array(
        'type' => 'fieldset',
        'title' => 'Настройки <a href="https://merchant.webmoney.ru/conf/default.asp" target="_blank">WebMoney</a>',
        'elements' => array(
          'active' => array(
            'type' => 'checkbox',
            'label' => 'Активировано',
          ),
          'secretKey' => array(
            'type' => 'text',
            'label' => 'Секретный ключ',
          ),
          'purse' => array(
            'type' => 'text',
            'label' => 'Кошелек WMU',
          ),
        ),
      ),
      //====== PRIVAT24
      'Privat24' => array(
        'type' => 'fieldset',
        'title' => 'Настройки <a href="https://privat24.ua/" target="_blank">Privat24</a>',
        'elements' => array(
          'active' => array(
            'type' => 'checkbox',
            'label' => 'Активировано',
          ),
          'secretKey' => array(
            'type' => 'text',
            'label' => 'Секретный ключ',
          ),
          'purse' => array(
            'type' => 'text',
            'label' => 'Валюта',
            'default' => 'UAH',
            'hint' => 'Возможные варианты: UAH, USD, EUR',
          ),
        ),
      ),
      //====== OTHER
      'other' => array(
        'type' => 'checkboxlist',
        'label' => 'Другие способы оплаты',
        'items' => array(
          1 => 'Оплата наличными',
          8 => 'Безналичный расчет',
        ),
      ),
    ),
  ),
  // global params schema and admin panel settings
  'hamster' => array(
    'admin' => array(
      'title' => 'Менеджер заказов',
      'description' => 'Модуль для обзора и управления заказами поступишвими в модуль магазина. Так же модуль позволяет вручную создать заказ за клиента, если он, к примеру, осуществляет его по телефону.',
      'db' => array(
        'version' => 1.1,
        'tables' => array(
          'order',
          'order_address',
          'order_check',
          'order_client',
        ),
      ),
    ),
  ),
);
