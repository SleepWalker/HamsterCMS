<?php
/**
 * This controller allows to apply into the contest
 */

namespace contest\controllers;

use contest\models\view\ApplyForm;
use contest\models\view\ConfirmForm;
use contest\models\Request;

class ContestController extends \Controller
{
    const FLASH_APPLY = 'contest.apply.success';

    public function actionApply()
    {
        $this->pageTitle = 'Заявка на участие в конкурсе - ' . \Yii::app()->name;

        $form = new ApplyForm();

        if ($this->processApplyForm($form)) {
            \Yii::app()->user->setFlash(self::FLASH_APPLY, true);
            $this->redirect('success');
        }

        $this->render('apply_form', array(
            'model' => $form,
        ));
    }

    public function actionSuccess()
    {
        if (\Yii::app()->user->getFlash(self::FLASH_APPLY)) {
            $this->render('success');
        } else {
            $this->redirect('apply');
        }
    }

    public function actionRules()
    {
        $this->render('rules');
    }

    public function actionConfirm()
    {
        $this->pageTitle = 'Страница финалиста - ' . \Yii::app()->name;

        $id = \Yii::app()->request->getParam('id');
        $key = \Yii::app()->request->getParam('key');
        if (!$id || !$key) {
            throw new \CHttpException(404, 'Not found');
        }

        $request = \contest\crud\RequestCrud::findByPk($id);
        if (!$request || !$request->isValidConfirmationKey($key)) {
            throw new \CHttpException(404, 'Not found');
        }

        $applyForm = $request->getApplyForm();
        $confirmForm = $request->getConfirmForm();

        if ($this->processConfirmForm($key, $applyForm, $confirmForm)) {
            \Yii::app()->user->setFlash(
                'success',
                'Спасибо, ваши данные успешно обработаны!'
            );
        }

        $this->render('confirm', [
            'confirmForm' => $confirmForm,
            'applyForm' => $applyForm,
        ]);
    }

    private function processConfirmForm($key, ApplyForm $applyForm, ConfirmForm $confirmForm)
    {
        $modelName = \CHtml::modelName($confirmForm);

        $confirmModelData = \Yii::app()->request->getPost($modelName);

        if ($confirmModelData && $this->isApplyFormSubmitted()) {
            $applyForm->load(\Yii::app()->request);
            $confirmForm->attributes = $confirmModelData;

            if ($confirmForm->validate() && $applyForm->validate()) {
                try {
                    $applyForm->request->confirm($key, $confirmForm);

                    \contest\crud\RequestCrud::update($applyForm->request);

                    return true;
                } catch (\Exception $e) {
                    \Yii::log('Error while confirming request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                    \Yii::app()->user->setFlash(
                        'error',
                        'Возникла не предвиденная ошибка! Пожалуйста, свяжитесь с нами.'
                    );

                    $this->refresh();
                }
            }
        }

        return false;
    }

    private function processApplyForm(ApplyForm $form)
    {
        if ($this->isApplyFormSubmitted()) {
            $form->load(\Yii::app()->request);

            if (\Yii::app()->request->isAjaxRequest && \Yii::app()->request->getPost('ajaxValidation')) {
                echo \CActiveForm::validate($form->getModels(), null, false);

                \Yii::app()->end();
            }

            if ($form->validate()) {
                try {
                    $request = \contest\crud\RequestCrud::create($form);

                    $this->sendNotifications($request);

                    return true;
                } catch (\Exception $e) {
                    \Yii::log('Error processing apply form: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                    \Yii::app()->user->setFlash(
                        'error',
                        'Во время обработки заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.'
                    );

                    $this->refresh();
                }
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    private function isApplyFormSubmitted()
    {
        return !!\Yii::app()->request->getPost(
            \CHtml::modelName(ApplyForm::class),
            false
        );
    }

    private function sendNotifications(Request $model)
    {
        $this->module->mailer->notifyMusicians($model, [
            'subject' => 'Заявка на участие в конкурсе',
            'view' => 'user_new_request',
        ]);

        $this->module->mailer->notifyAdmin([
            'subject' => 'Новая заявка на участие в конкурсе',
            'view' => 'admin_new_request',
            'viewData' => $model->attributes,
        ]);
    }
}
