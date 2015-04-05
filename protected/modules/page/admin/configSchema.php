<?php
return array(
    // modulewide params schema
    'moduleName' => array(
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Управление страницами',
    ),
    'moduleUrl' => array(
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'page',
    ),

    // global params schema and admin panel settings
    'hamster' => array(
        'admin' => array(
            'title' => 'Управление страницами',
            'description' => 'Модуль для управления страницами сайта',
            'db' => array(
                'version' => '1.0.0',
                'tables' => array(
                    'page',
                ),
            ),
            'routes' => array(
                'page/view',
            ),
        ),
    ),
);
