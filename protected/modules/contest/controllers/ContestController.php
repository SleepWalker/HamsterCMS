<?php
/**
 * This controller allows to apply into the contest
 */

namespace contest\controllers;

use contest\models\Request;
use contest\models\ContestId;
use contest\models\view\ApplyForm;
use hamster\models\UserId;
use hamster\components\exceptions\InvalidUserInputException;

class ContestController extends \Controller
{
    const FLASH_APPLY = 'contest.apply.success';

    public function actionApply()
    {
        \Yii::app()->language = 'uk';

        $this->pageTitle = 'Заява на участь у конкурсі - ' . \Yii::app()->name;

        $isAjaxValidation = \Yii::app()->request->isAjaxRequest
                    && \Yii::app()->request->getPost('ajaxValidation');

        if ($this->isApplyFormSubmitted()) {
            if ($isAjaxValidation) {
                $form = $this->module->factory->createApplyForm(
                    \Yii::app()->request
                );

                echo \CActiveForm::validate($form->getModels(), null, false);

                \Yii::app()->end();
            }

            try {
                $this->module->contestService->applyToContest(
                    new UserId(\Yii::app()->user->id),
                    \Yii::app()->request
                );

                \Yii::app()->user->setFlash(self::FLASH_APPLY, true);

                $this->redirect('success');
            } catch (InvalidUserInputException $ex) {
                $form = $ex->getModel();
            } catch (\Exception $ex) {
                \Yii::log('Error processing apply form: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                \Yii::app()->user->setFlash(
                    'error',
                    'Во время обработки заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.'
                );

                $this->refresh();
            }
        } else {
            $form = $this->module->factory->createApplyForm();
        }

        $this->render('apply_form', [
            'model' => $form,
            'isContest' => ContestId::IS_CONTEST,
        ]);
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

    public function actionFestRules()
    {
        $this->render('fest-rules');
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
        if (!$applyForm->request->isConfirmed()) {
            $applyForm->request->confirm($key);
            $applyForm->request->save();
        }

        if ($this->processConfirmForm($key, $applyForm)) {
            \Yii::app()->user->setFlash(
                'success',
                'Спасибо, ваши данные успешно обработаны!'
            );
        }

        $this->render('confirm', [
            'contestName' => '«Рок єднає нас» 2016',
            'applyForm' => $applyForm,
        ]);
    }

    private function processConfirmForm($key, ApplyForm $applyForm)
    {
        if ($this->isApplyFormSubmitted()) {
            $applyForm->load(\Yii::app()->request);

            if ($applyForm->validate()) {
                try {
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

    /**
     * @return boolean
     */
    private function isApplyFormSubmitted() : bool
    {
        return (bool) \Yii::app()->request->getPost(
            \CHtml::modelName(ApplyForm::class),
            false
        );
    }
}
