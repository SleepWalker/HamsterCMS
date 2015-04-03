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
        // TODO: minimum one email
        if ($this->postData) {
            if ($this->postData['type'] == 'group') {
                $model->scenario = 'group';
            }


            if (\Yii::app()->request->isAjaxRequest && \Yii::app()->request->getPost('ajaxValidation')) {
                echo \CActiveForm::validate($model);

                \Yii::app()->end();
            }

            $model->attributes = $this->postData;

            if ($model->save()) {
                $this->sendNotifications($model);

                return true;
            }
        }

        return false;
    }

    protected function getPostData()
    {
        $modelName = \CHtml::modelName('\contest\models\Request');

        return \Yii::app()->request->getPost($modelName);
    }

    protected function sendNotifications($model)
    {
        \Yii::app()->mail->send(array(
            'to' => $model->email,
            'subject' => 'Заявка на участие в конкурсе',
            'view' => 'user_new_request',
            'viewData' => $model->attributes,
        ));

        if (!empty(Yii::app()->params['adminEmail'])) {
            \Yii::app()->mail->send(array(
                'to' => $this->module->getAdminEmail(),
                'subject' => 'Новая заявка на участие в конкурсе',
                'view' => 'admin_new_request',
                'viewData' => $model->attributes,
            ));
        }
    }
}
