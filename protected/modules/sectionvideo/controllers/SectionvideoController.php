<?php
/**
 * SectionVideoController class for video module
 */

namespace sectionvideo\controllers;

use sectionvideo\models\Video;

class SectionvideoController extends \Controller
{
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

            $canonicalUrl = $this->createAbsoluteUrl('', ['id' => $id]);

            \Yii::app()->clientScript->registerLinkTag('canonical', null, $canonicalUrl);

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

    public function actionIndex()
    {
        $request = \Yii::app()->request;
        $page = (int)$request->getParam('page');
        $event = $request->getParam('event');
        $teacher = $request->getParam('teacher');

        $dataProvider = $this->getIndexDataProvider();
        $dataProvider->getData(); // force data fetching to get pagination info
        $pagesCount = $dataProvider->getPagination()->getPageCount();

        $query = [];

        if ($page > 1) {
            $query['page'] = min($page, $pagesCount); // ensure we are in the pages range
        }

        if ($teacher) {
            $query['teacher'] = $teacher;
        }

        if ($event) {
            $query['event'] = $event;
        }

        $canonicalUrl = $this->createAbsoluteUrl('', $query);

        if ($page === 1 || $page > $pagesCount) {
            $this->redirect($canonicalUrl, true, 301);
        }

        if ($page === 0) {
            $page = 1;
        }

        \Yii::app()->clientScript->registerLinkTag('canonical', null, $canonicalUrl);

        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($prevPage > 0) {
            $prevPageUrl = $this->createAbsoluteUrl('', array_merge($query, [
                'page' => $prevPage,
            ]));
            \Yii::app()->clientScript->registerLinkTag('prev', null, $prevPageUrl);
        }

        if ($nextPage <= $pagesCount) {
            $nextPageUrl = $this->createAbsoluteUrl('', array_merge($query, [
                'page' => $nextPage,
            ]));
            \Yii::app()->clientScript->registerLinkTag('next', null, $nextPageUrl);
        }


        $this->render('index', [
            'dataProvider' => $dataProvider,
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
