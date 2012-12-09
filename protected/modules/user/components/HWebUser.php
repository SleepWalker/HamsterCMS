<?php
class HWebUser extends CWebUser 
{
  public $loginUrl = array('site/login');
  public $logoutUrl = array('site/logout');

  public function init()
  {
    if(is_array($this->loginUrl) && count($this->loginUrl) == 1)
      $this->loginUrl = Yii::app()->createUrl($this->loginUrl[0]);
    if(is_array($this->logoutUrl) && count($this->logoutUrl) == 1)
      $this->logoutUrl = Yii::app()->createUrl($this->logoutUrl[0]);
    parent::init();
  }
}
