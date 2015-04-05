<?php
/**
 * This controller allows to apply into the contest
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\controllers;

class ContestController extends \Controller
{
    const FLASH_APPLY = 'contest.apply.success';

    public function actionApply()
    {
        $this->pageTitle = 'Заявка на участие в конкурсе';

        $model = new \contest\models\view\Request();

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

    protected function processModel(\contest\models\view\Request $model)
    {
        if ($this->postData) {
            if ($this->postData['type'] == \contest\models\view\Request::TYPE_GROUP) {
                $model->scenario = 'group';
            } else {
                $model->scenario = 'solo';
            }

            $this->feedModels($model);

            if (\Yii::app()->request->isAjaxRequest && \Yii::app()->request->getPost('ajaxValidation')) {
                echo \CActiveForm::validate(array_merge(
                    [$model],
                    $model->musicians,
                    $model->compositions
                ), null, false);

                \Yii::app()->end();
            }

            if ($model->validate()) {
                try {
                    \contest\crud\RequestCrud::save($model);
                } catch (\Exception $e) {
                    \Yii::app()->user->setFlash('error', 'Во время обработки заявки возникли не предвиденные ошибки. Пожалуйста попробуйте еще раз или свяжитесь с нами.');
                    $this->refresh();
                }
                $this->sendNotifications($model);

                return true;
            }
        }

        return false;
    }

    protected function getPostData()
    {
        $modelName = \CHtml::modelName('\contest\models\view\Request');

        return \Yii::app()->request->getPost($modelName);
    }

    private function feedModels($model)
    {
        $model->attributes = \Yii::app()->request->getPost(\CHtml::modelName($model), []);

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

    protected function sendNotifications($model)
    {
        // TODO: move into domain model
        foreach ($model->musicians as $musician) {
            if (!empty($musician->email)) {
                \Yii::app()->mail->send(array(
                    'to' => $musician->email,
                    'subject' => 'Заявка на участие в конкурсе',
                    'view' => 'user_new_request',
                    'viewData' => $musician->attributes,
                ));
            }
        }

        $adminEmail = $this->module->getAdminEmail();
        if (!empty($adminEmail)) {
            \Yii::app()->mail->send(array(
                'to' => $adminEmail,
                'subject' => 'Новая заявка на участие в конкурсе',
                'view' => 'admin_new_request',
                'viewData' => $model->attributes,
            ));
        }
    }
}
