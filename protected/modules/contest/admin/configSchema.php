<?php
return [
    // modulewide params schema
    'moduleName' => [
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Конкурсы',
    ],
    'moduleUrl' => [
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'contest',
    ],

    // global params schema and admin panel settings
    'hamster' => [
        'admin' => [
            'title' => 'Конкурсы',
            'description' => 'Модуль для управления конкурсами',
            'db' => [
                'version' => '1.5.1',
                'tables' => [
                    'contest_request',
                    'contest_musician',
                    'contest_composition',
                ],
            ],
            'routes' => [
                'contest/apply',
                'contest/rules',
                'contest/success',
                'contest/confirm',
            ],
        ],
    ],
];
