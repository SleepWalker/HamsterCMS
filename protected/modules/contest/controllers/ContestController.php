<?php
/**
 * This controller allows to apply into the contest
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    contest.controllers
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class ContestController extends Controller
{
    const FLASH_APPLY = 'contest.apply.success';

    public function actionApply()
    {
        $this->pageTitle = 'Заявка на участие в конкурсе';

        $model = new \contest\models\Request();

        if ($this->processModel($model)) {
            Yii::app()->user->setFlash(self::FLASH_APPLY, true);
            $this->redirect('success');
        }

        $this->render('apply_form', array(
            'model' => $model,
        ));
    }

    public function actionSuccess()
    {
        if (Yii::app()->user->getFlash(self::FLASH_APPLY)) {
            $this->render('success');
        } else {
            $this->redirect('apply');
        }
    }

    public function actionRules()
    {
        $this->render('rules');
    }

    protected function processModel($model)
    {
        if ($this->postData) {

            if ($this->postData['type'] == 'group') {
                $model->scenario = 'group';
            }


            if (Yii::app()->request->isAjaxRequest && isset($_POST['ajaxValidation'])) {
                echo CActiveForm::validate($model);

                Yii::app()->end();
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
        $modelName = CHtml::modelName('\contest\models\Request');

        return isset($_POST[$modelName]) ? $_POST[$modelName] : false;
    }

    protected function sendNotifications($model)
    {
        Yii::app()->mail->send(array(
            'to' => $model->email,
            'subject' => 'Заявка на участие в конкурсе',
            'view' => 'user_new_request',
            'viewData' => $model->attributes,
        ));

        if (!empty(Yii::app()->params['adminEmail'])) {
            Yii::app()->mail->send(array(
                'to' => Yii::app()->params['adminEmail'],
                'subject' => 'Новая заявка на участие в конкурсе',
                'view' => 'admin_new_request',
                'viewData' => $model->attributes,
            ));
        }
    }
}
