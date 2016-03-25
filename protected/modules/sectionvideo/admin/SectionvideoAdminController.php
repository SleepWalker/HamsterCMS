<?php
/**
 * Admin action class for sectionvideo module
 *
 * @package    hamster.modules.sectionvideo.admin.SectionvideoAdminController
 */

use sectionvideo\models\Instrument;
use sectionvideo\models\Musician;
use sectionvideo\models\Teacher;
use sectionvideo\models\Video;
use sectionvideo\models\VideoMusicians;
use event\models\Event;

class SectionvideoAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return [
            'index' => 'Все видео',
            'update' => [
                'name' => 'Редактирование видео',
                'display' => 'whenActive',
            ],
            'create' => [
                'name' => 'Добавить видео',
                'display' => 'index',
            ],
            'refreshcounters' => 'Обновить счетчики рейтинга',
        ];
    }

    /**
     * Создает или редактирует модель
     */
    public function actionUpdate()
    {
        if ($this->crudid) {
            $model = Video::model()->findByPk($this->crudid);
        } else {
            $model = new Video;
        }

        $musicians = count($model->musicians) > 0 ? $model->musicians : array(new VideoMusicians);

        $modelName = CHtml::modelName($model);
        $vmModelName = CHtml::modelName($musicians[0]);

        // TODO: Ajax валидация related полей
        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST[$modelName])) {
            $model->attributes = $_POST[$modelName];

            $transaction = Yii::app()->db->beginTransaction();
            try {
                $valid = $model->save();

                VideoMusicians::model()->deleteAllByAttributes(array('video_id' => $model->primaryKey));
                $musicians = array();
                if (isset($_POST['sortOrder'][$vmModelName])) {
                    $recentlyAddedIds = array(); // id добавленных в этой транзакции внешних связей
                    foreach ($_POST['sortOrder'][$vmModelName] as $oid => $postId) {
                        $data = $_POST[$vmModelName][$postId];

                        $musician_id = $data['musician_id'];
                        $instrument_id = $data['instrument_id'];
                        $teacher_id = $data['teacher_id'];

                        if (empty($musician_id)) {
                            $m = new Musician('simple');
                            $mData = $_POST[CHtml::modelName($m)][$postId];
                            if (!empty($mData['name'])) {
                                $m->name = $mData['name'];
                                $valid = $valid && $m->save();
                                $musician_id = $m->primaryKey;
                                $recentlyAddedIds[CHtml::modelName($m)][$mData['name']] = $m->primaryKey;
                            }
                        }

                        if (empty($instrument_id)) {
                            $m = new Instrument('simple');
                            $mData = $_POST[CHtml::modelName($m)][$postId];
                            if (!empty($mData['name'])) {
                                if (isset($recentlyAddedIds[CHtml::modelName($m)][$mData['name']])) {
                                    $instrument_id = $recentlyAddedIds[CHtml::modelName($m)][$mData['name']];
                                } else {
                                    $m->name = $mData['name'];
                                    $valid = $valid && $m->save();
                                    $instrument_id = $m->primaryKey;
                                    $recentlyAddedIds[CHtml::modelName($m)][$mData['name']] = $m->primaryKey;
                                }
                            }
                        }

                        if (empty($teacher_id)) {
                            $m = new Teacher('simple');
                            $mData = $_POST[CHtml::modelName($m)][$postId];
                            if (!empty($mData['fullName'])) {
                                if (isset($recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']])) {
                                    $teacher_id = $recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']];
                                } else {
                                    $parts = explode(' ', $mData['fullName']);
                                    $m->last_name = array_shift($parts);
                                    if (isset($parts[0])) {
                                        $m->first_name = array_shift($parts);
                                    }

                                    if (isset($parts[0])) {
                                        $m->middle_name = implode(' ', $parts);
                                    }

                                    $valid = $valid && $m->save();
                                    $teacher_id = $m->primaryKey;
                                    $recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']] = $m->primaryKey;
                                }
                            }
                        }

                        $vmModel = new VideoMusicians;
                        $vmModel->attributes = array(
                            'video_id' => $model->primaryKey,
                            'musician_id' => $musician_id,
                            'instrument_id' => $instrument_id,
                            'teacher_id' => $teacher_id,
                            'class' => $data['class'],
                            'sort_order' => $oid + 1,
                        );

                        $valid = $valid && $vmModel->save();

                        array_push($musicians, $vmModel);
                    }
                }

                if ($valid) {
                    $transaction->commit();
                } else {
                    $transaction->rollback();
                }

            } catch (Exception $e) {
                $transaction->rollback();
                $model->addError('composition_name', $e->getMessage());
                $valid = false;
            }
            if (count($musicians) == 0) {
                $musicians = count($model->musicians) > 0 ? $model->musicians : array(new VideoMusicians);
            }

            if (!$valid) {
                $musicians[0]->addError('musician_id', 'Ошибка при обработке данных музыкантов');
            }

        }

        $this->renderForm($model, array(
            'VideoMusicians' => $musicians,
        ));
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
        $model = new Video('search');
        $model->unsetAttributes();
        if (isset($_GET['Video'])) {
            $model->attributes = $_GET['Video'];
        }

        $tags = $model->tagModel()->findAll();
        $tagsMenu = array();
        foreach ($tags as $tag) {
            array_push($tagsMenu, $tag->name);
        }
        $this->aside = CMap::mergeArray($this->aside, array('Теги' => $tagsMenu));

        $this->render('table', array(
            'dataProvider' => $model->search(),
            'options' => array(
                'filter' => $model,
            ),
            'columns' => array(
                array(
                    'name' => 'image',
                    'value' => 'Chtml::image($data->thumbnail, $data->caption, array("width" => 100))',
                    'type' => 'raw',
                    'filter' => '',
                ),
                'fullTitle',
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
            $model = Video::model()->findByPk($this->crudid)->delete();
        } else {
            throw new CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

    }

    public function actionHrac()
    {
        Yii::import('ext.fields.jui.HRelationAutoComplete');
        HRelationAutoComplete::executeAction();
    }

    /**
     *  @return array JSON массив с тегами для jQuery UI AutoComplete
     */
    public function actionActags()
    {
        $tagsArr = Video::model()->suggestTags($_GET['term']);

        header('application/json');
        echo CJSON::encode($tagsArr);
    }

    /**
     *  @return array JSON массив с тегами для jQuery UI AutoComplete
     */
    public function actionAcevent()
    {
        $events = Event::model()->findAll([
            'condition' => 'name LIKE :keyword',
            'limit' => 10,
            'params' => [
                ':keyword' => '%' . strtr($_GET['term'], [
                    '%' => '\%',
                    '_' => '\_',
                    '\\' => '\\\\'
                ]) . '%',
            ],
        ]);

        foreach ($events as &$event) {
            $event = [
                'id' => $event->primaryKey,
                'value' => $event->name,
                'label' => $event->name,
            ];
        }

        header('application/json');
        echo CJSON::encode($events);
    }

    public function actionRefreshCounters()
    {
        try {
            \Yii::app()->getModule('sectionvideo')->ratingCalculator->refreshRatingCounters();
            \Yii::app()->user->setFlash('success', 'Счетчики успешно обновлены');
        } catch (\Exception $e) {
            \Yii::app()->user->setFlash('error', get_class($e) . ': ' . $e->getMessage());
        }
        $this->redirect('index');
    }
}
