<?php
/**
 * AdminController class for admin module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class AdminController extends \admin\components\HAdminController
{
    public function actionIndex()
    {
        $this->layout = 'main';
        $this->render('index');
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = \Yii::app()->errorHandler->error) {
            if (isset($_POST['ajax']) || isset($_POST['ajaxSubmit']) || isset($_POST['ajaxaction']) || isset($_POST['ajaxIframe']) || \Yii::app()->request->isAjaxRequest) {
                echo \CJSON::encode(array(
                    'action' => 404,
                    'content' => $error['message'],
                ));
            } else {
                $this->render('error', $error);
            }
        }
    }
}
