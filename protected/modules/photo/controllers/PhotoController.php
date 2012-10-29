<?php

class PhotoController extends Controller
{
  
  public function getAlbumVariants($id){
    $variants = array(null=>'');
    foreach(Photo::all($id) as $photo){
        $variants[$photo->id] = $photo->name;
    }
    return $variants;
  }
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
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
		array('allow',  // allow all users
				'users'=>array('*'),
			),
		);
	}

  /**
   * beforeRender инициализируем меню альбомов перед тем как запускать экшен
   * 
   * @param mixed $action 
   * @access protected
   * @return boolean
   */
  protected function beforeAction($action) 
  { 
    $this->menu = Album::model()->albumsMenu;
    return true; 
  }

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionAlbum($id)
	{
		$this->render('photo_index',array(
			'model'=>Album::model()->with('photo')->findByPk($id),
		));
	}
  
  /**
	 * Отображает страницу с информацией о фотографии
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('photo_view',array(
			'model'=>Photo::model()->with('album')->findByPk($id),
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
    $this->render('photo_index',array(
      'photos'=>Photo::model()->findAll(),
    ));
	}
}
