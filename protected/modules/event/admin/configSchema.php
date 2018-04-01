<?php
return [
    // modulewide params schema
    'moduleName' => [
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Мероприятия',
    ],
    'moduleUrl' => [
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'event',
    ],

    // global params schema and admin panel settings
    'hamster' => [
        'global' => [
        ],
        'admin' => [
            'title' => 'Мероприятия',
            'description' => 'Модуль позволяющий создавать мероприятия. В нем можно указать дату, время, длительность мероприятия, написать его описание и где оно находится, как туда добраться, а так же поставить отметку места провождения на карте.',
            'db' => [
                'version' => '1.2.1',
                'tables' => [
                    'event',
                ],
            ],
            'routes' => [
                'event/index',
                'event/view',
            ],
        ],
    ],
];
