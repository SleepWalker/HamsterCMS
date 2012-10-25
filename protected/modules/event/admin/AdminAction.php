<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    blog.controllers.blog.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends CAction
{
  public function run()
  {    
    // import the module-level models and components
		$this->controller->module->setImport(array(
			'event.models.*',
			'event.components.*',
		));
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все мероприятия',
      'update'  => array(
        'name' => 'Редактирование мероприятия',
        'display' => 'whenActive',
      ),
      'create'  => array(
        'name' => 'Добавить мероприятие',
        'display' => 'index',
      ),
    );
  }
  
  

  /**
	 * Создает или редактирует модель
	 */
  public function actionUpdate() 
  {	  
    if ($this->controller->crudid)
      $model=Event::model()->findByPk($this->controller->crudid);
    else
      $model = new Event;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Event']))
		{

			$model->attributes=$_POST['Event'];
      
			if ($model->save()) 
      {      
        $saved = true;
			}
			//else
			//  throw new CHttpException(404,'Ошибка при сохранении');
		}
		
		if($_POST['ajaxIframe'] || $_POST['ajaxSubmit'])
    {
      // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
      if($saved && $this->controller->crud == 'create')
        $data = array(
          'action' => 'redirect',
          'content' => $this->controller->curModuleUrl . 'update/'.$model->id,
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->controller->renderPartial('update',array(
                         'model'=>$model,
                       ), true, true),
        );
      
      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
      Yii::app()->end();
    }
		
		if(!$_POST['ajaxSubmit'])
      $this->controller->render('update',array(
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
  
  /**
   *  Выводит таблицу всех товаров
   */
  public function actionIndex() 
  {
		$this->controller->render('table', array(
			'dataProvider'=> new CActiveDataProvider(Event),
			'columns'=>array(
			  'eventId',
        'name',
        'where',
        'start_date',
        'end_date',
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
		$uploadPath = $_SERVER['DOCUMENT_ROOT'].Event::uploadsUrl;
		if(Yii::app()->request->isEventRequest)
		{
			// we only allow deletion via POST request
			$model = Event::model()->findByPk($this->controller->crudid);
			// Удаляем изображение
		  if(file_exists($uploadPath.$model->image)) unlink($uploadPath.$model->image);
			$model->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	} 

  /**
   *  @return array JSON массив для jQuery UI AutoComplete
   */
  public function actionActags()
  {
    $tag = Tag::model()->string2array($_GET['term']); // работаем только с последним тегом из списка
    $tagsArr = Tag::model()->suggestTags(array_pop($tag));
    array_walk($tagsArr, function (&$value, $index) {
      $value = '"' . $value . '"';
    });
    echo '[' . implode(', ', $tagsArr) . ']';
  }
} 
?>