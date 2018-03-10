<?php
/**
 * This controller allows to apply into the contest
 */

namespace contest\controllers;

use contest\models\Request;
use contest\models\ContestId;
use contest\models\Contest;
use contest\models\Settings;
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

        $contest = $this->module->contestService->getActiveContest();

        if (!$contest || !$contest->canApply()) {
            $this->redirect('/');
            \Yii::app()->end();
        }

        $request = \Yii::app()->request;
        $isAjaxValidation = $request->isAjaxRequest && $request->getPost('ajaxValidation');
        $form = $this->module->factory->createApplyForm($request);

        if ($form->isFormRequest($request)) {
            if ($isAjaxValidation) {
                echo \CActiveForm::validate($form->getModels(), null, false);

                \Yii::app()->end();
            }

            try {
                $this->module->contestService->applyToContest(
                    new UserId(\Yii::app()->user->id),
                    $form
                );

                \Yii::app()->user->setFlash(self::FLASH_APPLY, true);

                $this->redirect('success');
            } catch (InvalidUserInputException $e) {
                $form = $e->getModel();
            } catch (\Exception $e) {
                \Yii::log('Error processing apply form: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                \Yii::app()->user->setFlash(
                    'error',
                    'Во время обработки заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.'
                );

                $this->refresh();
            }
        }

        $this->render('apply_form', [
            'model' => $form,
            'contestName' => $contest->title,
            'isContest' => $contest->type === Contest::TYPE_CONTEST,
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

    public function actionRequest()
    {
        $this->pageTitle = 'Страница финалиста - ' . \Yii::app()->name;

        $id = (int) \Yii::app()->request->getParam('id');
        $key = \Yii::app()->request->getParam('key');

        try {
            $request = $this->module->contestService->getRequest($id, $key);
        } catch (\Throwable $e) {
            throw new \CHttpException(404, 'Not found');
        }

        $applyForm = $request->getApplyForm();
        $contest = $request->contest;
        $httpRequest = \Yii::app()->request;

        if ($applyForm->isFormRequest($httpRequest)) {
            $applyForm->load($httpRequest);

            try {
                $this->module->contestService->updateRequest($applyForm);

                \Yii::app()->user->setFlash(
                    'success',
                    'Спасибо, ваши данные успешно обработаны!'
                );
            } catch (InvalidUserInputException $e) {
                $applyForm = $e->getModel();
            } catch (\Exception $e) {
                \Yii::log('Error while updating request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

                \Yii::app()->user->setFlash(
                    'error',
                    'Возникла не предвиденная ошибка! Пожалуйста, свяжитесь с нами.'
                );

                $this->refresh();
            }
        }

        $this->render('confirm', [
            'contestName' => $contest->title,
            'applyForm' => $applyForm,
        ]);
    }

    public function actionConfirm()
    {
        $id = (int) \Yii::app()->request->getParam('id');
        $key = \Yii::app()->request->getParam('key');

        try {
            $this->module->contestService->confirmRequest($id, $key);
        } catch (\InvalidArgumentException $e) {
            throw new \CHttpException(404, 'Not found');
        } catch (\Throwable $e) {
            \Yii::log('Error confirming request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

            \Yii::app()->user->setFlash(
                'error',
                'Во время подтверждения заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.'
            );

            throw new \CHttpException(500, 'Internal error');
        }

        $this->redirect(['request', 'id' => $id, 'key' => $key]);
    }
}
