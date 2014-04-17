<?php
/**
 * Event module main controller file 
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    event.controllers.EventController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class EventController extends Controller
{
  /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view', 'ical'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
  
  /**
	 * Lists all models.
	 */ 
	public function actionIndex()
	{
    $dataProvider = new CActiveDataProvider('Event', array(

		));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}
  
  /**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}
  
  /**
	 * Генерирует и отправляет файл iCalendar для встречи с $id
	 * @param integer $id встречи
	 */
  public function actionIcal($id)
  {
    Yii::import('application.vendors.iCalcreator.*');
    require_once 'iCalcreator.class.php';
    $event = $this->loadModel($id);
    
    $config    = array(
      "unique_id" => $event->eventId . "@" . $_SERVER['SERVER_NAME'] ,
      'filename' => 'event'.$event->eventId.'_' . $_SERVER['SERVER_NAME'] . '.ics',
    );
    $vcalendar = new vcalendar( $config );
    $vevent     = & $vcalendar->newComponent( "vevent" );
    $vevent->setProperty( "created");
    $vevent->setProperty( "description", $event->desc );
    $vevent->setProperty( "LOCATION", $event->location );
    $vevent->setProperty( "summary", $event->location );
    $vevent->setProperty( "dtstamp", array('timestamp' => time() ));
    $vevent->setProperty( "dtstart", array('timestamp' => strtotime($event->start_date)) );
    $vevent->setProperty( "dtend", array('timestamp' => strtotime($event->end_date)) );
    $vevent->setProperty( "method",        "PUBLISH" );
    $vevent->setProperty( "x-wr-calname",  $event->name );
    $vevent->setProperty( "X-WR-CALDESC",  $event->location );
    $vevent->setProperty( "organizer", Yii::app()->name);
    $vevent->setProperty( "url", $event->viewUrl);
    $uuid      = "212BF46D-41FF-4E29-91D9-2787329B4797";
    $vevent->setProperty( "X-WR-RELCALID", $uuid );
    $vevent->setProperty( "X-WR-TIMEZONE", "Europe/Ukraine" );
    $vcalendar->sort();
    
    $utf8Encode = FALSE;
    if( isset( $_SERVER["HTTP_ACCEPT_ENCODING"] ) &&
      ( FALSE !== strpos( strtolower( $_SERVER["HTTP_ACCEPT_ENCODING"] ), "gzip" )))
      $gzip     = TRUE;
    else
      $gzip     = FALSE;
    $vcalendar->returnCalendar($utf8Encode, $gzip);
  }
  
  /**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Event::model()->findByEventId($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
