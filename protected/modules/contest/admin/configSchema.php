<?php
return array(
    // modulewide params schema
    'moduleName' => array(
        'label' => 'Название модуля',
        'type' => 'text',
        'default' => 'Конкурсы',
    ),
    'moduleUrl' => array(
        'label' => 'URI Адрес модуля',
        'type' => 'text',
        'default' => 'contest',
    ),

    // global params schema and admin panel settings
    'hamster' => array(
        'admin' => array(
            'title' => 'Конкурсы',
            'description' => 'Модуль для управления конкурсами',
            'db' => array(
                'version' => '1.0',
                'tables' => array(
                    'contest_request',
                ),
            ),
            'routes' => array(
                // 'sectionvideo/index',
                // 'sectionvideo/view',
            ),
        ),
    ),
);