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
