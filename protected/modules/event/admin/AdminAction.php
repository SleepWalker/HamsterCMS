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
      
      $model->save();
		}

    $this->renderForm($model);
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
			'dataProvider'=> new CActiveDataProvider('Event'),
      'columns'=>array(
        'eventId',
        'name',
        'where',
        array(
          'name' => 'start_date',
          'type' => 'datetime',
        ),
        array(
          'name' => 'end_date',
          'type' => 'datetime',
        ),
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
