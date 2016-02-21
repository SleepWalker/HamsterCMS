<?php
/**
 * This controller allows to apply into the contest
 */

namespace user\controllers;

use user\models\User;
use user\models\LoginForm;

class UserController extends \Controller
{
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'oauth' => [
                'class' => 'ext.hoauth.HOAuthAction',
                'model' => User::class,
                'attributes' => [
                    'email' => 'email',
                    'first_name' => 'firstName',
                    'last_name' => 'lastName',
                    'is_active' => 1,
                ],
            ],
        ];
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        if (!\Yii::app()->user->isGuest) {
            $this->redirect();
        }

        $renderType = isset($_GET['ajax']) ? 'renderPartial' : 'render';
        if (isset($_GET['ajax'])) {
            \Yii::app()->clientscript->scriptMap['jquery.js'] = \Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
        }

        $model = new LoginForm();
        $modelName = \CHtml::modelName($model);

        // ставим по умолчанию галочку rememberMe
        $model->rememberMe = 1;

        // collect user input data
        if (\Yii::app()->request->getPost($modelName)) {
            $model->attributes = \Yii::app()->request->getPost($modelName);

            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                if (!\Yii::app()->request->isAjaxRequest) {
                    $this->redirect();
                } else {
                    echo 'ok';
                    \Yii::app()->end();
                }
            }
        } else {
            // страница, на которую вернется пользователь.
            \Yii::app()->user->returnUrl = \Yii::app()->getRequest()->urlReferrer;
        }

        // display the login form
        $this->{$renderType}('login', [
            'model' => $model
        ], false, !empty($_GET['ajax']));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        \Yii::app()->user->logout();
        $this->redirect();
    }

    /**
     *  Регистрация пользователя
     **/
    public function actionRegister()
    {
        \Yii::import('application.modules.user.models.*');

        $this->pageTitle = 'Регистрация';

        if (!\Yii::app()->user->isGuest) {
            $this->redirect('/');
        }

        $model = new User('register');

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            $model->attributes = $_POST['User'];
            echo CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if (isset($_POST['User'])) {
            $model->attributes = $_POST['User'];

            if ($model->save()) {
                $model->sendMailConfirm(); // Отправляем письмо с ссылкой подтверждения Email

                if (isset($_POST['User']['role'])) {
                    // включена возможность выбирать роли при регистрации
                    // перенаправим обработку этого выбора на модель AuthItem
                    $authItem = AuthItem::model()->addToTransfer($model, $_POST['User']['role']);
                }

                $this->renderText('
         <h1>Успешная регистрация</h1>
         <p>Ваш аккаунт был успешно зарегистрирован. Вскоре на ваш почтовый ящик придет письмо с ссылкой для активации аккаунта.</p>
         <p>Вернуться на <a href="/">главную страницу</a> или воспользоваться формой входа:</p>
         <p><a href="/user/login">Войти на сайт</a></p>
         ');
                \Yii::app()->end();
            }
        }

        $this->render('register', array(
            'model' => $model,
        ));
    }

    /**
     *  Подтверждение адресса Email
     **/
    public function actionConfirm()
    {
        $model = User::model()->findByEmail($_GET['email']);
        if ($model && !$model->isActive) {
            if ($_GET['h'] == $model->confirmHash) {
                $model->isActive = 1;
                if ($model->save()) {
                    $code = 1;
                }

            } else {
                $model->sendMailConfirm(); // Отправляем письмо с ссылкой подтверждения Email
                $code = 2;
            }
        }

        /**
         * @var $code код результата проверки емейла
         *    0 - Такого эмейла не существует
         *    1 - Успешная активация
         *    2 - Аккаунт уже активирован
         */
        $this->render('confirm', array('code' => $code));
    }

    /**
     * Форма смены пароля
     */
    public function actionChpass()
    {
        $email = isset($_POST['User']['email']) ? $_POST['User']['email'] : $_GET['email'];

        $code = 1;
        if (!empty($email)) {
            $model = User::model()->findByEmail($email);
            if (!$model->isActive) {
                $code = 0; // пользователи с не активированным мылом не могут восстановить пароль
                unset($_GET);
                unset($_POST);
            }
        } else {
            $model = new User;
        }

        if ($model && $_GET['h'] == $model->chpassHash) {
            $model->scenario = 'register';
            // Сохраняем новый пароль
            if (isset($_POST['User'])) {
                $model->attributes = $_POST['User'];

                if ($model->save()) {
                    $code = 5;
                    unset($_GET);
                    unset($_POST);
                }
            } else {
                // выводим форму для смены пароля
                $code = 4;
            }

        } else {
            $code = 0;
        }

        if ($_POST['User'] && !isset($_POST['h'])) {
            if ($model) {
                $model->sendChpassMail();
                $code = 3;
            } else {
                $code = 2;
            }

        }

        /**
         * @var $code код статуса операции смены пароля
         *    0 - ошибка восстановления пароля (не правильный хэш)
         *    1 - необходимо запросить емейл пользователя
         *    2 - такого емейла не существует
         *    3 - сообщение о том, что емейл был отправлен
         *    4 - запрос нового пароля
         *    5 - успешная смена пароля
         */
        $this->render('chpass', array(
            'model' => $model,
            'code' => $code,
        ));
    }

    /**
     *  Редирект
     **/
    public function redirect($referrer = false, $terminate = true, $statusCode = 302)
    {
        if (!$referrer) {
            $referrer = '/' . str_replace(\Yii::app()->createAbsoluteUrl('/'), '', \Yii::app()->getRequest()->urlReferrer);
            $requestUri = \Yii::app()->request->requestUri;
            if ($referrer == $requestUri && \Yii::app()->getRequest()->urlReferrer) {
                $referrer = \Yii::app()->user->returnUrl;
            } elseif (\Yii::app()->getRequest()->urlReferrer) {
                $referrer = \Yii::app()->request->urlReferrer;
            } else {
                $referrer = '/';
            }

        }

        parent::redirect($referrer);
    }
}
