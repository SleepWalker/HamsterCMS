<?php
use Pimple\Container;

$container = new Container();

$container['mailer'] = function () {
    return new \contest\components\Mailer(
        \Yii::app()->mail,
        \Yii::app()->getModule('contest')->getAdminEmail()
    );
};

$container['requestCrud'] = function () {
    return new \contest\crud\RequestCrud();
};

$container['contestService'] = function ($c) {
    return new \contest\components\ContestService(
        $c['factory'],
        $c['mailer'],
        $c['requestCrud'],
        \Yii::app()->user
    );
};

$container['factory'] = function () {
    return new \contest\components\Factory();
};

return $container;
