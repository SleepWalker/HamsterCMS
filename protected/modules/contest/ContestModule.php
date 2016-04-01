<?php
/**
 * Contest module
 *
 * @package    contest
 */

class ContestModule extends CWebModule
{
    public $controllerNamespace = '\contest\controllers';

    private $container;

    public function init()
    {
        $this->container = require(__DIR__ . '/container.php');
    }

    public function getMailer()
    {
        return $this->container['mailer'];
    }

    public function getAdminEmail()
    {
        return isset($this->params['adminEmail'])
            ? $this->params['adminEmail']
            : Yii::app()->params['adminEmail'];
    }
}
