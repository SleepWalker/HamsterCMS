<?php
/**
 * Site controller class.
 * Provides contact and error displaying functionality
 *
 * @package application.controllers
 */

class SiteController extends \Controller
{
    public $layout = '//layouts/column3';
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
                'foreColor' => 0x980d0d,
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        //$this->render('index');
        throw new CHttpException(404, 'Запрашиваемая страница не существует.');
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (isset($_POST['ajax']) || isset($_POST['ajaxSubmit']) || isset($_POST['ajaxaction']) || isset($_POST['ajaxIframe'])) {
                echo CJSON::encode(array(
                    'action' => 404,
                    'content' => $error['message'],
                ));
            } else {
                $this->render('error', $error);
            }

        }
    }

    public function actionJsError()
    {
        $error = \CHtml::encode(\Yii::app()->request->getPost('error'));
        $source = \CHtml::encode(\Yii::app()->request->getPost('source'));
        $line = \Yii::app()->request->getPost('line');
        $col = \Yii::app()->request->getPost('col');
        $stack = \CHtml::encode(\Yii::app()->request->getPost('stack'));
        $location = \CHtml::encode(\Yii::app()->request->getPost('location'));

        if (!is_numeric($line) || !is_numeric($col)) {
            throw new \DomainException('Wrong line and col format');
        }

        if (filter_var($location, FILTER_VALIDATE_URL) === false) {
            throw new \DomainException('Wrong url format');
        }

        $message = "JsError with message $error in $source:$line:$col\n\nStack trace:\n$stack\n\nwindow.location=$location\n---";

        \Yii::log($message, \CLogger::LEVEL_ERROR, 'js');
    }

    /**
     * Displays the contact page
     */
    public function actionContact()
    {
        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $emailWasSent = Yii::app()->mail->send(array(
                    'from' => $model->email,
                    'to' => \Yii::app()->params['adminEmail'],
                    'subject' => $model->getSubject(),
                    'view' => 'mail_contact',
                    'viewData' => $model->attributes,
                    'attachments' => $model->getFiles(),
                ));

                if ($emailWasSent) {
                    Yii::app()->user->setFlash('success', 'Спасибо за ваше письмо. Мы ответим при первой же возможности.');
                } else {
                    Yii::app()->user->setFlash('error', 'Отправка письма не может быть выполнена, проверте правильность введенных данных');
                }
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }
}
