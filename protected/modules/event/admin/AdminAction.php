<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.blog.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
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
    if ($this->crudid)
      $model=Event::model()->findByPk($this->crudid);
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
      Yii::app()->end();
    }
		
		if(!$_POST['ajaxSubmit'])
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
  
  /**
   *  Выводит таблицу всех товаров
   */
  public function actionIndex() 
  {
		$this->render('table', array(
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
    if(!Yii::app()->request->isPostRequest || !Event::model()->deleteByPk($this->crudid))
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
    Yii:app()->end();
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