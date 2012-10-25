<?php
/**
 * Controller class for static page displaying
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PageController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column3';
	private $_model;

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
				'actions'=>array('index', 'error'/*,'view'*/),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Показует страницу
	 */
	public function actionIndex($partial = false)
	{
	  $render = $partial ? 'renderPartial' : 'render';
		$model=$this->loadModel(array('full_path'=>'/' . $_GET['path']));
		// если $partial = true мы возвращаем строку вьюхи, вместо прямого вывода в браузер
    return $this->$render('index',array(
        'model'=>$model,
    ), $partial);
	}
	
	/**
	 * Возвращает содержимое страницы для ajax запросов
	 * Этот метод вызывается классом ApiController
	 */
	public function api($path)
	{
		$_GET['path'] = implode('/', $path);
    return $this->actionIndex(true);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel(array $param)
  {
    if($this->_model===null)
    {
      $this->_model=Page::model()->findByAttributes($param);
      
      if($this->_model===null)
        throw new CHttpException(404,'Запрашиваемая страница не существует.');
    }
    return $this->_model;
  }
	
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='page-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
