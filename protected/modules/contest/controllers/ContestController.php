<?php
/**
 * This controller allows to apply into the contest
 */

namespace contest\controllers;

use contest\models\view\ApplyForm;
use contest\models\Request;

class ContestController extends \Controller
{
    const FLASH_APPLY = 'contest.apply.success';

    public function actionApply()
    {
        $this->pageTitle = 'Заявка на участие в конкурсе - ' . \Yii::app()->name;

        $model = new ApplyForm();

        // TODO: use builder pattern for creating new entity (?)
        if ($this->processModel($model)) {
            \Yii::app()->user->setFlash(self::FLASH_APPLY, true);
            $this->redirect('success');
        }

        $this->render('apply_form', array(
            'model' => $model,
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

        // TODO: переместить как параметры экшена, когда роутер позволит это
        $id = \Yii::app()->request->getParam('id');
        $key = \Yii::app()->request->getParam('key');
        if (!$id || !$key) {
            throw new \CHttpException(404, 'Not found');
        }

        $request = \contest\crud\RequestCrud::findByPk($id);
        if (!$request || !$request->isValidConfirmationKey($key)) {
            $this->redirect('/');
        }

        $requestViewModel = $request->getViewModel();

        $model = $request->getConfirmViewModel();
        $modelName = \CHtml::modelName($model);

        if (($data = \Yii::app()->request->getPost($modelName)) && $this->postData) {
            $this->feedRequest($requestViewModel);
            $model->attributes = $data;

            if ($model->validate() && $requestViewModel->validate()) {
                $transaction = \Yii::app()->db->beginTransaction();
                try {
                    // TODO: confirm should be moved to DM
                    $request->confirm($key, $model);

                    $request->name = $requestViewModel->name;

                    // TODO: DDD - это боль. отрефактори и это, заодно
                    foreach ($requestViewModel->compositions as $index => $composition) {
                        $compositionRecord = $request->compositions[$index];
                        $compositionRecord->attributes = $composition->attributes;
                        if (!$compositionRecord->save()) {
                            throw new \Exception('Error saving composition: ' . var_export($request->errors, true));
                        }
                    }

                    foreach ($requestViewModel->musicians as $index => $musician) {
                        $musicianRecord = $request->musicians[$index];
                        $musicianRecord->attributes = $musician->attributes;
                        if (!$musicianRecord->save()) {
                            throw new \Exception('Error saving musician: ' . var_export($request->errors, true));
                        }
                    }

                    // TODO
                    // \contest\crud\RequestCrud::save($request);
                    if (!$request->save()) {
                        throw new \Exception('Error saving: ' . var_export($request->errors, true));
                    }

                    \Yii::app()->user->setFlash('success', 'Спасибо, ваши данные успешно обработаны!');
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \Yii::app()->user->setFlash('error', 'Возникла не предвиденная ошибка! Пожалуйста, свяжитесь с нами.');
                    \Yii::log('Error while confirming request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);
                }
                $this->refresh();
            }
        }

        $this->render('confirm', [
            'model' => $model,
            'request' => $requestViewModel,
        ]);
    }

    private function processModel(ApplyForm $model)
    {
        if ($this->postData) {
            // TODO
            if ($this->postData['type'] == Request::TYPE_GROUP) {
                $model->scenario = 'group';
            } else {
                $model->scenario = 'solo';
            }

            $this->feedRequest($model);

            if (\Yii::app()->request->isAjaxRequest && \Yii::app()->request->getPost('ajaxValidation')) {
                echo \CActiveForm::validate($model->getModels(), null, false);

                \Yii::app()->end();
            }

            if ($model->validate()) {
                try {
                    $request = \contest\crud\RequestCrud::create($model);

                    $this->sendNotifications($request);

                    return true;
                } catch (\Exception $e) {
                    \Yii::app()->user->setFlash('error', 'Во время обработки заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.');
                    \Yii::log($e->getMessage(), \CLogger::LEVEL_ERROR);
                    $this->refresh();
                }
            }
        }

        return false;
    }

    protected function getPostData()
    {
        $modelName = \CHtml::modelName(Request::class);

        return \Yii::app()->request->getPost($modelName);
    }

    private function feedRequest(ApplyForm $model)
    {
        $model->request->attributes = \Yii::app()->request->getPost(\CHtml::modelName(Request::class), []);

        $compositionsData = \Yii::app()->request->getPost(\CHtml::modelName($model->compositions[0]), []);
        $compositions = $model->compositions;
        foreach ($compositionsData as $index => $compositionData) {
            $compositions[$index]->attributes = $compositionData;
        }

        $musiciansData = \Yii::app()->request->getPost(\CHtml::modelName($model->musicians[0]), []);
        $musicians = $model->musicians;
        foreach ($musiciansData as $index => $musicianData) {
            $musicians[$index]->attributes = $musicianData;
        }
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
