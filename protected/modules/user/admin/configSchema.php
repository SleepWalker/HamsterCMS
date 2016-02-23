<?php
return [
    'hamster' => [
        'admin' => [
            'title' => 'Пользователи и группы',
            'description' => 'Модуль позволяет просматривать зарегистрированных пользователей системы. Управлять группами и перемещать пользователей в них. Совершать рассылки зарегистрированным или оставившим свой email пользователям.',
            'db' => [
                'version' => '1.1.2',
                'tables' => [
                    'AuthAssignment',
                    'AuthItem',
                    'AuthItemChild',
                    'auth_user',
                    'user_identity',
                    'YiiSession',
                ],
            ],
            'routes' => [
                'user/login',
                'user/logout',
                'user/register',
            ],
        ],
    ],
];
