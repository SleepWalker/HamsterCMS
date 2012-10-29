<?php
return array(  
  // global params schema and admin panel settings
  'hamster' => array(
    'global' => array(),
    'admin' => array(
      'title' => 'Фотоальбомы',
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
