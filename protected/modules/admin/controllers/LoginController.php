<?php
/**
 * LoginController class for admin module
 *
 * Заменяет собой страницу входа на сайт
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers.LoginController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class LoginController extends \admin\components\HAdminController
{
    public $adminAssetsUrl;

    public function filters()
    {
        return [];
    }

    public function actionIndex()
    {
        $this->layout = 'empty';

        if (\Yii::app()->user->checkAccess('admin')) {
            $this->redirect(\Yii::app()->createUrl('admin/admin/index'));
        } elseif (!\Yii::app()->user->isGuest) {
            $this->redirect(array('/'));
        }

        $model = new \LoginForm();

        // ставим по умолчанию галочку rememberMe
        $model->rememberMe = 1;

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // Проверяем введенные юзером данные
            $valid = $model->validate() && $model->login();
            // Пишим в лог все удачные и неудачные попытки
            $message = $valid ? "Удачная" : "Не удачная";
            $message .= " попытка зайти в ПУ Hamster под логином '{$model->user_email}' с ip: {$_SERVER['REMOTE_ADDR']}";
            // очищаем массив POST, так как нам не нужно в логах показывать информацию о вводимых данных
            unset($_POST);
            \Yii::log($message, 'info', 'hamster.login');

            if ($valid) {
                $this->redirect(\Yii::app()->user->returnUrl);
            }

        }

        $this->render('index', array('model' => $model));
    }
}
