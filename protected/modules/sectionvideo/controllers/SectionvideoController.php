<?php
/**
 * SectionVideoController class for video module
 */

namespace sectionvideo\controllers;

use sectionvideo\models\Video;

class SectionvideoController extends \Controller
{
    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $model = $this->loadModel($id);
        if (isset($_GET['ajax'])) {
            $data = [
                'content' => $this->renderPartial('view', [
                    'model' => $model,
                ], true, true),
                'title' => $this->pageTitle,
            ];
            header('Content-Type: application/json');
            echo \CJSON::encode($data);
            \Yii::app()->end();
        } else {
            \Yii::app()->openGraph->registerMeta([
                'type' => 'video',
                'url' => $model->getViewUrl(),
                'title' => $model->getFullTitle(),
                'image' => $model->getImageSrc('full'),
                'updatedTime' => $model->date_create,
            ]);

            $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    public function getIndexDataProvider()
    {
        $criteria = new \CDbCriteria();

        if (isset($_GET['tag'])) {
            $criteria->compare('tags', $_GET['tag']);
        }

        if (isset($_GET['event'])) {
            $criteria->compare('event', $_GET['event']);
        }

        if (isset($_GET['teacher'])) {
            $criteria->distinct = true;
            $criteria->join .= ' LEFT JOIN {{section_video_musicians}} vMusician ON vMusician.video_id = t.id';
            $criteria->join .= ' LEFT JOIN {{section_teacher}} teacher ON teacher.id = vMusician.teacher_id';
            $criteria->compare('teacher.id', $_GET['teacher'], false);
        }

        return new \CActiveDataProvider(Video::model()->published(), [
            'pagination' => [
                'pageSize' => 20,
                'route' => 'index',
                'pageVar' => 'page',
            ],
            'sort' => [
                'attributes' => [
                    'likes' => [
                        'asc' => 'likes ASC, views ASC',
                        'desc' => 'likes DESC, views DESC',
                        'default' => 'desc',
                    ],
                    'date_create' => [
                        'default' => 'desc',
                    ],
                ],
                'defaultOrder' => [
                    'likes' => 'desc',
                ],
                'sortVar' => 'sort',
            ],
            'criteria' => $criteria,
        ]);
    }

    /**
     * Lists all models.
     * А так же фильтрует модели по тегам из $_GET['tag']
     */
    public function actionIndex()
    {
        $this->render('index', [
            'dataProvider' => $this->getIndexDataProvider(),
        ]);
    }

    public function actionLike()
    {
        $videoId = \Yii::app()->request->getPost('id');

        try {
            $likes = $this->module->ratingCalculator->addLike($videoId);

            header('Content-Type: application/json');

            echo \CJSON::encode([
                'status' => 'success',
                'data' => [
                    'likes' => $likes,
                ],
            ]);
        } catch (\Exception $e) {
            throw new \CHttpException(400, 'Wrong request');
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        try {
            return $this->module->videoRepository->get($id);
        } catch (\Exception $e) {
            throw new \CHttpException(404, 'The requested page does not exist.');
        }
    }
}
