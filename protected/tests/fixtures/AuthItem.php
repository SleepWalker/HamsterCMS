<?php
return array(
  array('name' => 'admin','type' => '2','description' => 'Super User','bizrule' => NULL,'data' => 'N;'),
  array('name' => 'guest','type' => '2','description' => 'Гости','bizrule' => 'return Yii::app()->user->isGuest;','data' => 'N;'),
  array('name' => 'staff','type' => '2','description' => 'Managers of Shop','bizrule' => NULL,'data' => 'N;'),
  array('name' => 'transfer','type' => '2','description' => 'Пользователи, которые ожидают переноса в группу, выбранную ими при регистрации','bizrule' => NULL,'data' => 'N;'),
  array('name' => 'user','type' => '2','description' => 'Зарегистрированные пользователи','bizrule' => 'return !Yii::app()->user->isGuest;','data' => 'N;'),
);
