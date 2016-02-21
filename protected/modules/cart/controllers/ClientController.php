<?php
/**
 * Контроллер для функции "Перезвоните мне" и "Задать вопрос"
 *
 * @package    hamster.modules.cart.controllers.ClientController
 */

use user\models\User;

class ClientController extends \Controller
{
    /**
     * actionRecall
     *
     * @param mixed $id
     * @access public
     * @return void
     */
    public function actionRecall($id = false)
    {
        $form = new RecallForm;
        // ajax валидация
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['RecallForm'])) {
            $form->attributes = $_POST['RecallForm'];
            if ($form->validate()) {
                User::mailAdmin(array(
                    'application.modules.cart.views.client.mail',
                    'data' => $id ? Shop::model()->findByPk($id) : false,
                    'name' => $form->name,
                    'phone' => $form->phone,
                ), 'Запрос звонка');
                $this->renderPartial('success');
                Yii::app()->end();
            }
        }

        $this->renderPartial('form', array(
            'cform' => $form->CForm,
        ), false, true);
    }

    /**
     * actionQuestion
     *
     * @param mixed $id
     * @access public
     * @return void
     */
    public function actionQuestion($id = false)
    {
        $form = new QuestionForm;
        // ajax валидация
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['QuestionForm'])) {
            $form->attributes = $_POST['QuestionForm'];
            if ($form->validate()) {
                User::mailAdmin(array(
                    'application.modules.cart.views.client.mail',
                    'data' => $id ? Shop::model()->findByPk($id) : false,
                    'name' => $form->name,
                    'phone' => $form->phone,
                    'email' => $form->email,
                    'question' => $form->question,
                ), 'Запрос звонка');
                $this->renderPartial('success');
                Yii::app()->end();
            }
        }

        $this->renderPartial('form', array(
            'cform' => $form->CForm,
        ), false, true);
    }
}
