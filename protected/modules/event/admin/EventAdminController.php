<?php
/**
 * Admin action class for event module
 */

use event\models\Event;
use hamster\models\UploadedFile;

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
            $model = Event::model()->findByPk($this->crudid);
        } else {
            $model = new Event();
        }

        $this->ajaxValidate($model);

        $this->saveIfSubmitted($model);

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
        $this->render('table', [
            'dataProvider' => new \CActiveDataProvider(Event::CLASS),
            'columns' => [
                // TODO: need reusable column class to show such images
                [
                    'name' => 'image',
                    'value' => '$data->hasImage() ? \CHtml::image($data->image->getAdminThumbUrl()) : ""',
                    'type'=>'raw',
                ],
                'eventId',
                'name',
                'where',
                [
                    'name' => 'start_date',
                    'type' => 'datetime',
                ],
                [
                    'name' => 'end_date',
                    'type' => 'datetime',
                ],
            ],
        ]);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete()
    {
        if (!\Yii::app()->request->isPostRequest || !Event::model()->deleteByPk($this->crudid)) {
            throw new \CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

        \Yii::app()->end();
    }
}
