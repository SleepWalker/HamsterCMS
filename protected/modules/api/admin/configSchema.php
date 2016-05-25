<?php
return [
    // modulewide params schema
    'moduleName' => [
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Api',
    ],
    'moduleUrl' => [
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'api',
    ],

    // global params schema and admin panel settings
    'hamster' => [
        'admin' => [
            'title' => 'Api',
            'description' => 'Модуль предоставляющий функционал API для других модулей',
            'db' => [
                'version' => 0,
                'tables' => [
                ],
            ],
            'routes' => [
                'index'
            ],
        ],
    ],
];
