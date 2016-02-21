<?php
class HWebUser extends CWebUser
{
    public $loginUrl = ['/user/login'];
    public $logoutUrl = ['/user/logout'];
    public $registerUrl = ['/user/register'];

    public function init()
    {
        if (is_array($this->loginUrl) && count($this->loginUrl) == 1) {
            $this->loginUrl = Yii::app()->createUrl($this->loginUrl[0]);
        }

        if (is_array($this->logoutUrl) && count($this->logoutUrl) == 1) {
            $this->logoutUrl = Yii::app()->createUrl($this->logoutUrl[0]);
        }

        if (is_array($this->registerUrl) && count($this->registerUrl) == 1) {
            $this->registerUrl = Yii::app()->createUrl($this->registerUrl[0]);
        }

        parent::init();
    }
}
