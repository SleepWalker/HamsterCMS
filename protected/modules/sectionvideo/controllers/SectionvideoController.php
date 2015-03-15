<?php
/**
 * SectionVideoController class for video module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sectionvideo.controllers
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

use hamster\modules\sectionvideo\models\Video as Video;

class SectionvideoController extends Controller
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
            header('application/json');
            echo CJSON::encode($data);
            Yii::app()->end();
        } else {
        	$this->render('view', array(
                'model' => $this->loadModel($id),
            ));
        }

    }

    public function getIndexDataProvider()
    {
        $criteria = new CDbCriteria();

        if (isset($_GET['tag'])) {
            $criteria->addSearchCondition('tags', $_GET['tag']);
        }

        if (isset($_GET['event'])) {
            $criteria->addSearchCondition('event', $_GET['event']);
        }

        if (isset($_GET['teacher'])) {
            $criteria->addSearchCondition('teacher', $_GET['teacher']);
        }

        return new CActiveDataProvider(Video::model(), array(
            'pagination' => array(
                'pageSize' => 20,
                'route' => 'index',
            ),
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
            'dataProvider' => $this->indexDataProvider,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model = Video::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }
}
