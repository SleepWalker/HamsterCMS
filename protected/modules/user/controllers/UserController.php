<?php
/**
 * This controller allows to apply into the contest
 */

namespace user\controllers;

use user\models\Identity;
use user\models\User;
use user\models\RegisterForm;
use user\models\LoginForm;
use user\components\PasswordHash;

class UserController extends \Controller
{
    public function filters()
    {
        return ['accessControl'];
    }

    public function accessRules()
    {
        return [
            ['deny',
                'actions' => ['login', 'register'],
                'users' => ['@'],
            ],
            ['deny',
                'actions' => ['profile', 'logout'],
                'users' => ['?'],
            ],
        ];
    }

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

        $this->pageTitle = 'Вход — ' . \Yii::app()->name;
        $this->breadcrumbs = ['Вход'];

        $renderType = isset($_GET['ajax']) ? 'renderPartial' : 'render';
        if (isset($_GET['ajax'])) {
            \Yii::app()->clientscript->scriptMap['jquery.js'] = \Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
        }

        $loginForm = new LoginForm();
        $modelName = \CHtml::modelName($loginForm);
        $data = \Yii::app()->request->getPost($modelName);

        // ставим по умолчанию галочку rememberMe
        $loginForm->rememberMe = 1;

        // collect user input data
        if ($data) {
            $loginForm->attributes = $data;

            // validate user input and redirect to the previous page if valid
            if ($loginForm->validate() && $loginForm->login()) {
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
            'model' => $loginForm
        ], false, !empty($_GET['ajax']));
    }

    public function actionProfile()
    {
        $userId = \Yii::app()->user->id;
        $model = User::model()->findByPk($userId);
        $modelName = \CHtml::modelName($model);

        if (!$model) {
            \Yii::log('Can not find user by id: ' . $userId, CLogger::LEVEL_ERROR);
            $this->redirect('/');
            \Yii::app()->end();
        }

        if (\Yii::app()->request->getPost('ajax')) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        $data = \Yii::app()->request->getPost($modelName);
        if ($data) {
            $model->attributes = $data;
            $model->uploadedPhoto = \CUploadedFile::getInstance($model, 'uploadedPhoto');

            if ($model->validate()) {
                $canBeSaved = true;

                if ($model->uploadedPhoto) { // TODO: shoould be implemented
                    try {
                        $file = \Yii::app()->fileStorage->store($model->uploadedPhoto, 'user/photo');

                        // prepare preview

                        $model->photo_id = $file->primaryKey;

                        $photo = \Yii::app()->fileStorage->get($model->photo, new TeacherPhotoStrategy());

                        $model->photo->getUrl();
                    } catch (\Exception $e) {
                        $canBeSaved = false;
                        \Yii::log('Can not save user photo: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);
                    }
                }

                if ($canBeSaved) {
                    $model->save();
                    \Yii::app()->user->setFlash('success', 'Профиль успешно сохранен');
                    $this->refresh();
                    \Yii::app()->end();
                }
            }
        }

        $this->render('profile', [
            'model' => $model,
        ]);
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
        if (!\Yii::app()->user->isGuest) {
            $this->redirect('/');
        }

        $this->pageTitle = 'Регистрация — ' . \Yii::app()->name;
        $this->breadcrumbs = ['Регистрация'];

        $identityKey = \Yii::app()->request->getParam('identityKey');
        $identity = \Yii::app()->session->get($identityKey);
        if (!$identity) {
            $this->renderRegisterForm();
        } else {
            $form = new RegisterForm();
            $form->attributes = $identity;
            if ($form->validate()) {
                $this->renderProfileForm($identityKey, $form);
            } else {
                throw new \DomainException('Bronken identity data');
            }
        }
    }

    private function renderRegisterForm()
    {
        $model = new RegisterForm();

        $modelName = \CHtml::modelName($model);
        $data = \Yii::app()->request->getPost($modelName);

        if (\Yii::app()->request->getPost('ajax')) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if ($data) {
            $model->attributes = $data;

            if ($model->validate()) {
                $identityKey = uniqid();

                \Yii::app()->session->add(
                    $identityKey,
                    $model->attributes
                );

                $this->redirect(['register', 'identityKey' => $identityKey]);
            }
        }

        $model->resetPasswords();
        $this->render('register_identity', [
            'model' => $model,
        ]);
    }

    private function renderProfileForm($identityKey, RegisterForm $form)
    {
        $model = new User();

        $modelName = \CHtml::modelName($model);
        $data = \Yii::app()->request->getPost($modelName);

        if (\Yii::app()->request->getPost('ajax')) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if ($data) {
            $model->attributes = $data;
            $model->email = $form->email;

            $transaction = \Yii::app()->db->beginTransaction();

            try {
                $this->saveOrThrow($model);

                $identity = new Identity();
                $identity->public = $form->email;
                $identity->private = PasswordHash::from($form->password)->getValue();
                $identity->user_id = $model->primaryKey;
                $identity->provider = Identity::PROVIDER_DEFAULT;

                $this->saveOrThrow($identity);

                if (isset($data['role'])) {
                    // включена возможность выбирать роли при регистрации
                    // перенаправим обработку этого выбора на модель AuthItem
                    $authItem = \AuthItem::model()->addToTransfer(
                        $model,
                        $data['role']
                    );
                }

                $model->sendMailConfirm(); // TODO: test this mail

                $transaction->commit();

                $this->render('registerSuccess');

                \Yii::app()->end();
            } catch (\Exception $e) {
                $transaction->rollBack();

                \Yii::log('Error adding request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                throw new \Exception('Error saving data', 0, $e);
            }
        }

        $this->render('register_profile', [
            'model' => $model,
            'identityKey' => $identityKey,
        ]);
    }

    private function saveOrThrow(\CActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \Exception(
                'Error saving ' . get_class($model) . ': '
                . var_export($model->getErrors(), true)
            );
        }
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
