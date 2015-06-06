<?php
return array(
  // modulewide params schema
  'moduleName' => array(
    'label' => 'Название модуля',
    'type' => 'text',
    'default' => 'Видео',
  ),
  'moduleUrl' => array(
    'label' => 'URI Адрес модуля',
    'type' => 'text',
    'default' => 'video',
  ),

  // global params schema and admin panel settings
  'hamster' => array(
    'admin' => array(
      'title' => 'Видео секции',
      'description' => 'Модуль реализирующий функционал видео-каталога со специфическими для музыкальной секции модификациями',
      'db' => array(
        'version' => '1.2.0',
        'tables' => array(
          'section_video',
          'section_video_tag',
          'section_musician',
          'section_instrument',
          'section_teacher',
          'section_school',
          'section_video_musician',
        ),
      ),
      'routes' => array(
        'sectionvideo/index',
        'sectionvideo/view',
      ),
    ),
  ),
);
