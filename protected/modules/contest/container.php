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
        $c['mailer'],
        new \contest\crud\RequestCrud()
    );
};

return $container;
