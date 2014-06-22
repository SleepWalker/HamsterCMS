<?php
/**
 * Admin action file for page controller
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.controllers.page.PageAdminController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PageAdminController extends HAdminController
{
  /**
	 * @return меню для табов
	 */
  public function tabs()
  {
    return array(
      ''  => 'Все страницы',
      'update'  => array(
        'name' => 'Редактирование страницы',
        'display' => 'whenActive',
      ),
      'create'  => 'Добавить страницу',
    );
  }

  /**
	 * Создает или редактирует модель
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
  public function actionUpdate() 
  {
    if (!empty($this->crudid))
      $model=Page::model()->findByPk($this->crudid);
    else
      $model = new Page;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Page']))
		{
			$model->attributes=$_POST['Page'];
			  
			if(!$model->save())
			  throw new CHttpException(404,'Ошибка при сохранении');
        
      $saved = true;
		}
		if(isset($_POST['ajaxSubmit']))
    {
      if($saved && $this->crud == 'create')
        $data = array(
          'action' => 'redirect',
          'content' => $this->curModuleUrl . 'update/'.$model->id,
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->renderPartial('update',array(
            'model'=>$model,
          ), true, true),
        );
      
      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    else
      $this->render('update',array(
			  'model'=>$model,
		  ));
  }
  
  /**
	 * Перенаправляет обработку запроса на действие Update
	 */
  public function actionCreate() 
  {
    $this->actionUpdate();
  }
  
  
  public function actionIndex() 
  {
    $dataProvider=new CActiveDataProvider('Page', array(
	      'pagination' => array(
          'pageSize'=>Yii::app()->params['defaultPageSize'],
        ),
	    )
	  );
		$this->render('table',array(
			'dataProvider'=>$dataProvider,
			'columns'=>array(
        'full_path', 
        'title',  
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
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			Page::model()->findByPk($this->crudid)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			/*if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));*/
		}
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}  
}
?>
