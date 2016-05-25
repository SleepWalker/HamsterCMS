<?php
/**
 * Admin action class for contest module
 *
 * @package hamster.modules.api.admin
 */

class ApiAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return [
            'index' => 'Api',
        ];
    }

    public function actionIndex()
    {
        echo 'Hi, I`m API';
    }
}
