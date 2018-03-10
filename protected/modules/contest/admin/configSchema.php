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
                'version' => '1.6.2',
                'tables' => [
                    'contest_store',
                    'contest_contest',
                    'contest_request',
                    'contest_musician',
                    'contest_composition',
                ],
            ],
            'routes' => [
                'contest/request',
                'contest/apply',
                'contest/rules',
                'contest/festRules',
                'contest/success',
                'contest/confirm',
            ],
        ],
    ],
];
