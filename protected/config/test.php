<?php

return \CMap::mergeArray(
    require (dirname(__FILE__) . '/main.php'),
    array(
        'components' => array(
            // 'fixture' => array(
            //     'class' => 'system.test.CDbFixtureManager',
            //     'basePath' => dirname(__FILE__) . '/fixtures',
            // ),
            // 'db' => array(
            //     'connectionString' => 'mysql:host=localhost;dbname=hamster_test',
            //     'username' => 'root',
            //     'password' => '',
            // ),
        ),
    )
);
