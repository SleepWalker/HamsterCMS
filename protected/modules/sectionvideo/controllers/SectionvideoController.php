<?php
/**
 * SectionVideoController class for video module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace sectionvideo\controllers;

class SectionvideoController extends \Controller
{
    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        if (isset($_GET['ajax'])) {
            $data = array(
                'content' => $this->renderPartial('view', array(
                    'model' => $this->loadModel($id),
                ), true, true),
                'title' => $this->pageTitle,
            );
            header('Content-Type: application/json');
            echo \CJSON::encode($data);
            \Yii::app()->end();
        } else {
            $this->render('view', array(
                'model' => $this->loadModel($id),
            ));
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

        return new \CActiveDataProvider('\sectionvideo\models\Video', array(
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
                    'likes' => \CSort::SORT_DESC,
                ],
                'sortVar' => 'sort',
            ],
            'criteria' => $criteria,
        ));
    }

    /**
     * Lists all models.
     * А так же фильтрует модели по тегам из $_GET['tag']
     */
    public function actionIndex()
    {
        $this->render('index', array(
            'dataProvider' => $this->getIndexDataProvider(),
        ));
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
