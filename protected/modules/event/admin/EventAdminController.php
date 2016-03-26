<?php
/**
 * Admin action class for event module
 *
 * @package    Hamster.modules.event.admin.EventAdminController
 */

class EventAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return [
            'index' => 'Все мероприятия',
            'update' => [
                'name' => 'Редактирование мероприятия',
                'display' => 'whenActive',
            ],
            'create' => [
                'name' => 'Добавить мероприятие',
                'display' => 'index',
            ],
        ];
    }

    /**
     * Создает или редактирует модель
     */
    public function actionUpdate()
    {
        if ($this->crudid) {
            $model = \event\models\Event::model()->findByPk($this->crudid);
        } else {
            $model = new \event\models\Event();
        }

        $modelName = \CHtml::modelName($model);

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if (isset($_POST[$modelName])) {
            $model->attributes = $_POST[$modelName];

            $model->save();
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

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionIndex()
    {
        $this->render('table', array(
            'dataProvider' => new \CActiveDataProvider('\event\models\Event'),
            'columns' => array(
                'eventId',
                'name',
                'where',
                array(
                    'name' => 'start_date',
                    'type' => 'datetime',
                ),
                array(
                    'name' => 'end_date',
                    'type' => 'datetime',
                ),
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
        if (!\Yii::app()->request->isPostRequest || !\event\models\Event::model()->deleteByPk($this->crudid)) {
            throw new \CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

        \Yii::app()->end();
    }
}
