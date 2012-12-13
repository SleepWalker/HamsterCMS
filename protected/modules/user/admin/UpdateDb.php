<?php

class UpdateDb extends HUpdateDb
{
  public function verHistory()
  {
    return array(0, 1, 1.1);
  }
  
  public function update1()
  {
    $am = Yii::app()->authManager;
          
    // добавляем дефолтную роль user
    $bizRule='return !Yii::app()->user->isGuest;';
    $am->createRole('user', 'Зарегистрированные пользователи', $bizRule);
    
    // добавляем дефолтную роль guest
    $bizRule='return Yii::app()->user->isGuest;';
    $am->createRole('guest', 'Гости', $bizRule);
  }
  
  public function update1_1()
  {
    $am = Yii::app()->authManager;
          
    // добавляем дефолтную роль transfer
    $am->removeAuthItem('transfer');
    $am->createRole('transfer', 'Пользователи, которые ожидают переноса в группу, выбранную ими при регистрации');
  }
}
