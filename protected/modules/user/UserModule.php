<?php
/**
 * User module main file
 *
 * @package    hamster.modules.user.UserModule
 */

class UserModule extends CWebModule
{
    public $controllerNamespace = '\user\controllers';

    public function init()
    {
        $this->setImport([
            'user.models.AuthItem',
            'user.models.AuthAssignment',
        ]);
    }
}
