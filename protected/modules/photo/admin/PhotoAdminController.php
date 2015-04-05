<?php
/**
 * Admin action class for photo module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @author     Evgeniy Pochekuev
 * @package    Hamster.modules.photo.admin.PhotoAdminController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PhotoAdminController extends \admin\components\HAdminController
{
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все фото',
      'update'  => array(
        'name' => 'Редактирование фото',
        'display' => 'whenActive',
      ),
      'create'  => array(
        'name' => 'Добавить фото',
        'display' => 'index',
      ),
      'album'  => 'Альбомы',
      'album/create'  => array(
        'name' => 'Создать альбом',
        'display' => 'album',
      ),
      'album/update'  => array(
        'name' => 'Редактирование альбома',
        'display' => 'whenActive',
      ),
    );
  }

	/**
	 * Creates/updates a new model.
	 * If creation is successful, the browser will be redirected to the 'update' page.
	 */
	public function actionUpdate()
	{
		if ($this->crudid)
      $model=Photo::model()->findByPk($this->crudid);
    else
      $model = new Photo;

		// AJAX валидация
		if(isset($_POST['ajax']))
		{
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

    if(isset($_POST['Photo']))
		{
			$model->attributes=$_POST['Photo'];

			if($model->save())
			{
        $saved = true;
      }
		}

    $model->uImage = $model->photo;

    if($_POST['ajaxIframe'] || $_POST['ajaxSubmit'])
    {
      // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
      if($saved && $this->crud == 'create')
        $data = array(
          'action' => 'redirect',
          'content' => $this->createUrl('update', 'id' => $model->id),
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->renderPartial('update',array(
                         'model'=>$model,
                       ), true, true),
        );

      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
      Yii::app()->end();
    }

		if(!$_POST['ajaxSubmit'])
      $this->render('update',array(
			  'model'=>$model,
		  ));
	}
	public function actionCreate()
	{
		$this->actionUpdate();
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest && Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			$model = Photo::model()->findByPk($this->crudid)->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex($id=null)
	{
		$model=new Photo('search');
    if($id)$model->album_id = $id;
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Photo']))
			$model->attributes=$_GET['Photo'];

		$this->render('table',array(
			'dataProvider'=>$model->search(),
      'options' => array(
			 'filter'=>$model,
			),
      'columns'=>array(
			  array(
            'name'=>'photo',
            'value'=>'$data->img("thumb")',
            'type'=>'raw',
            'filter'=>'',
            'sortable'=>false,
        ),
			  'id',
        'name',
        array(
          'name' => 'album',
          'value' => '$data->album->name',
        ),
      ),
		));
	}

	/**
	 * Creates/updates a new model.
	 * If creation is successful, the browser will be redirected to the 'update' page.
	 */
	public function actionAlbumUpdate()
	{
		if ($this->crudid)
      $model=Album::model()->findByPk($this->crudid);
    else
      $model = new Album;

		// AJAX валидация
		if(isset($_POST['ajax']))
		{
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Album']))
		{
			$model->attributes=$_POST['Album'];
      if ($model->save())
      {
        $saved = true;
			}
		}

    if($_POST['ajaxIframe'] || $_POST['ajaxSubmit'])
    {
      // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
      if($saved && $this->crud == 'create')
        $data = array(
          'action' => 'redirect',
          'content' => $this->createUrl('album/update', 'id' => $model->id),
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->renderPartial('update',array(
            'model'=>$model,
          ), true, true),
        );

      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
      Yii::app()->end();
    }

    if(!$_POST['ajaxSubmit'])
      $this->render('update',array(
        'model'=>$model,
      ));
  }
  public function actionAlbumCreate()
	{
		$this->actionAlbumUpdate();
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionAlbumDelete()
	{
		if(Yii::app()->request->isPostRequest && Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			$model = Album::model()->findByPk($this->crudid)->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}


	/**
	 * Manages all models.
	 */
	public function actionAlbum()
	{
		$model=new Album('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Album']))
			$model->attributes=$_GET['Album'];

		$this->render('table',array(
			'dataProvider'=>$model->search(),
      'options' => array(
			 'filter'=>$model,
			),
      'columns'=>array(
			  'id',
        'name',
			  /*array(
            'name'=>'photo',
            'value'=>'count($data->photo) ? $data->img(45) : ""',
            'type'=>'raw',
            'filter'=>'',
        ),*/
      ),
		));
	}

}
