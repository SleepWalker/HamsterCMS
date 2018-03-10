<?php
use Pimple\Container;

use contest\components\RequestRepository;

$container = new Container();

$container['mailer'] = function () {
    return new \contest\components\Mailer(
        \Yii::app()->mail,
        new RequestRepository(),
        \Yii::app()->getModule('contest')->getAdminEmail()
    );
};

$container['contestService'] = function ($c) {
    return new \contest\components\ContestService(
        $c['factory'],
        $c['mailer'],
        new \contest\crud\RequestCrud(),
        \Yii::app()->user
    );
};

$container['factory'] = function () {
    return new \contest\components\Factory();
};

return $container;
