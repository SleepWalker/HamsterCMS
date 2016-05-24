<?php
return [
    // modulewide params schema
    'moduleName' => [
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Видео',
    ],
    'moduleUrl' => [
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'video',
    ],

    // global params schema and admin panel settings
    'hamster' => [
        'admin' => [
            'title' => 'Видео секции',
            'description' => 'Модуль реализирующий функционал видео-каталога со специфическими для музыкальной секции модификациями',
            'db' => [
                'version' => '1.4.0',
                'tables' => [
                    'section_instrument',
                    'section_musician',
                    'section_school',
                    'section_teacher',
                    'section_video',
                    'section_video_musicians',
                    'section_video_rating',
                    'section_video_tag',
                ],
            ],
            'routes' => [
                'sectionvideo/index',
                'sectionvideo/view',
            ],
        ],
    ],
];
