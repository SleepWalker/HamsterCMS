<?php
return array(  
  'showOnIndex' => array(
    'label' => 'На главной модуля размещать',
    'type' => 'dropdownlist',
    'items' => array(
      'photos' => 'Все фотографии',
      'album' => 'Все альбомы',
    ),
  ),
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(),
    'admin' => array(
      'title' => 'Фотоальбомы',
      'description' => 'Модуль позволяющий представить любую граффическую информацию на сайте. Это могут быть фотоальбомы, галереи, портфолио, страницы с информацией о сотрудниках и т.д.',
      'db' => array(
        'version' => 1,
        'tables' => array(
          'photo',
          'photo_album',
        ),
      ),
    ),
  ),
);
