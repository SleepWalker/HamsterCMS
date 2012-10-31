<?php
return array(
  'bccEmails'=> array(
    'label' => 'Email адреса менеджеров, которым будут отправляться скрытые копии писем с информацией о заказе (через запятую)',
    'type' => 'text',
  ),
  // global params schema and admin panel settings
  'hamster' => array(
    'admin' => array(
      'title' => 'Менеджер заказов',
      'description' => 'Модуль для обзора и управления заказами поступишвими в модуль магазина. Так же модуль позволяет вручную создать заказ за клиента, если он, к примеру, осуществляет его по телефону.',
      'bd' => array(
        'version' => 1,
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
