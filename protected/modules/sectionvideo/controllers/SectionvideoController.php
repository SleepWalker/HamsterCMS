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
use hamster\modules\sectionvideo\models\VideoMusicians as VideoMusicians;
use hamster\modules\sectionvideo\models\Teacher as Teacher;
use hamster\modules\sectionvideo\models\Musician as Musician;
use hamster\modules\sectionvideo\models\Instrument as Instrument;

class SectionvideoController extends Controller
{

	/**
	 * @return array action filters
	 */
/*	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
  }*/

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
/*public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view', 'rss'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
  }*/

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		if(isset($_GET['ajax']))
		{
			$data = array(
				'content' => $this->renderPartial('view',array(
						'model'=>$this->loadModel($id),
					), true, true),
				'title' => $this->pageTitle,
				);
			header('application/json');
			echo CJSON::encode($data);
			Yii::app()->end();
		}else
			$this->actionIndex();
	}
  
	public function getIndexDataProvider()
	{
		$criteria=new CDbCriteria();
    
		if(isset($_GET['tag']))
			$criteria->addSearchCondition('tags',$_GET['tag']);

		if(isset($_GET['event']))
			$criteria->addSearchCondition('event', $_GET['event']);

		if(isset($_GET['teacher']))
			$criteria->addSearchCondition('teacher', $_GET['teacher']);

		return new CActiveDataProvider(Video::model(), array(
			'pagination'=>array(
				'pageSize'=> 30,
				'route' => 'index',
			),
			'criteria'=>$criteria,
		));
  }

	/**
	 * Lists all models.
	 * А так же фильтрует модели по тегам из $_GET['tag']
	 */ 
	public function actionIndex()
	{
		$this->render('index',array(
			'dataProvider'=>$this->indexDataProvider,
		));
	}
  
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Video::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
