<?php
return array(  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(),
    'admin' => array(
      'title' => 'Фотоальбомы',
      'description' => 'Модуль позволяющий представить любую граффическую информацию на сайте. Это могут быть фотоальбомы, галереи, портфолио, страницы с информацией о сотрудниках и т.д.',
      'bd' => array(
        'version' => 1,
        'tables' => array(
          'photo',
          'photo_album',
        ),
      ),
    ),
  ),
);
