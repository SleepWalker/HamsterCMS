<?php
/**
 * Admin action file for page controller
 *
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class PageAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return array(
            '' => 'Все страницы',
            'update' => array(
                'name' => 'Редактирование страницы',
                'display' => 'whenActive',
            ),
            'create' => 'Добавить страницу',
        );
    }

    /**
     * Создает или редактирует модель
     * If update is successful, the browser will be redirected to the 'view' page.
     */
    public function actionUpdate()
    {
        if (!empty($this->crudid)) {
            $model = \page\models\Page::model()->findByPk($this->crudid);
        } else {
            $model = new \page\models\Page();
        }

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        $modelName = \CHtml::modelName($model);
        $postData = \Yii::app()->request->getPost($modelName);
        if ($postData) {
            $model->attributes = $postData;

            if (!$model->save()) {
                throw new CHttpException(404, 'Ошибка при сохранении');
            }
        }

        $this->renderForm($model);
    }

    /**
     * Перенаправляет обработку запроса на действие Update
     */
    public function actionCreate()
    {
        $this->actionUpdate();
    }

    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('\page\models\Page', array(
            'pagination' => array(
                'pageSize' => Yii::app()->params['defaultPageSize'],
            ),
        ));
        $this->render('table', array(
            'dataProvider' => $dataProvider,
            'columns' => array(
                'full_path',
                'title',
            ),
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete()
    {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            \page\models\Page::model()->findByPk($this->crudid)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            /*if(!isset($_GET['ajax']))
        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));*/
        } else {
            throw new CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }
    }
}
