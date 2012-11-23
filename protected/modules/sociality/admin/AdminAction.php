<?php
/**
 * Admin action class for sociality module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sociality.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'sociality.models.*',
			'sociality.components.*',
		));
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все комментарии',
    );
  }
  
  /**
   *  Выводит таблицу всех комментариев
   */
  public function actionIndex() 
  {
    // TODO: сортировка по дате
    // TODO: нормальное отображение имени модели
    // TODO: ссылки на viewUrl модели
    $model=new Comment('search');
    $model->unsetAttributes();
    if(isset($_GET['Comment']))
      $model->attributes=$_GET['Comment'];
	  
		$this->render('table',array(
			'dataProvider'=> $model->search(),
      'buttons' => array('delete'),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
        array(
          'name' => 'id',
          'type'=>'raw',
          'value'=>'CHtml::link($data->id, $data->model->viewUrl, array("target"=>"_blank"))',
        ),
        'model_pk',
        'model_id',
        'comment',
        'name',
        'email',
        /*array(            
            'name'=>'status',
            'type'=>'raw',
            'value' => '$data->statusName',
            'filter'=> Post::getStatusNames(),
          ),
        array(            
            'name'=>'user_search',
            'value' => '$data->user->first_name',
          ),*/
        // Using CJuiDatePicker for CGridView filter
        // http://www.yiiframework.com/wiki/318/using-cjuidatepicker-for-cgridview-filter/
        // http://www.yiiframework.com/wiki/345/how-to-filter-cgridview-with-from-date-and-to-date-datepicker/
        // http://www.yiiframework.com/forum/index.php/topic/20941-filter-date-range-on-cgridview-toolbar/
        /*array(            
            'name'=>'add_date',
            'value' => 'str_replace(" ", "<br />", Yii::app()->dateFormatter->formatDateTime($data->add_date))',
            'type' => 'raw',
            'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_add_from', 
              'language' => 'ru',
              'defaultOptions' => array(  
                'showOn' => 'focus', 
                'showOtherMonths' => true,
                'selectOtherMonths' => true,
                'changeMonth' => true,
                'changeYear' => true,
                'showButtonPanel' => true,
                'autoSize' => true,
                'dateFormat' => "yy-mm-dd",
              )
            ), true)
            .
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_add_to', 
              'language' => 'ru',
            ), true),
          ),*/
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
			$model = Comment::model()->findByPk($this->crudid)->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	} 
} 
?>
