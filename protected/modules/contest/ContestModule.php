<?php
/**
 * Contest module
 *
 * @package contest
 */

use contest\components\Mailer;
use contest\components\ContestService;

class ContestModule extends \CWebModule
{
    public $controllerNamespace = '\contest\controllers';

    private $container;

    public function init()
    {
        $this->container = require(__DIR__ . '/container.php');
    }

    public function getMailer() : Mailer
    {
        return $this->container['mailer'];
    }

    public function getContestService() : ContestService
    {
        return $this->container['contestService'];
    }

    public function getAdminEmail() : string
    {
        return isset($this->params['adminEmail'])
            ? $this->params['adminEmail']
            : Yii::app()->params['adminEmail'];
    }
}
