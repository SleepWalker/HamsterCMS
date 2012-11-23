<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    blog.controllers.blog.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'blog.models.*',
			'blog.components.*',
		));
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все материалы',
      'update'  => array(
        'name' => 'Редактирование материала',
        'display' => 'whenActive',
      ),
      'create'  => array(
        'name' => 'Добавить материал',
        'display' => 'index',
      ),
      'categorie'  => 'Управление категориями',
    );
  }
  
  

  /**
	 * Создает или редактирует модель
	 */
  public function actionUpdate() 
  {
    if ($this->crudid)
      $model=Post::model()->findByPk($this->crudid);
    else
      $model = new Post;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Post']))
		{

			$model->attributes=$_POST['Post'];

      if($model->save())
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
    $model=new Post('search');
    $model->unsetAttributes();
    if(isset($_GET['Post']))
      $model->attributes=$_GET['Post'];
      
    $tags = Tag::model()->findAll();
    $tagsMenu = array();
    foreach($tags as $tag)
    {
      array_push($tagsMenu, $tag->name);
    }
    $this->aside['Теги'] = $tagsMenu;
	  
		$this->render('table',array(
			'dataProvider'=> $model->latest()->search(),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
			  array(            
            'name'=>'image',
            'value'=>'$data->img("thumb")',
            'type'=>'raw',
            'filter'=>'',
        ),
        'title',
        array(            
            'name'=>'cat_id',
            'value' => '$data->cat->name',
            'filter'=> Categorie::model()->catsList,
        ),
        array(            
            'name'=>'status',
            'type'=>'raw',
            'value' => '$data->statusName',
            'filter'=> Post::getStatusNames(),
        ),
        array(            
            'name'=>'user_search',
            'value' => '$data->user->first_name',
        ),
        // Using CJuiDatePicker for CGridView filter
        // http://www.yiiframework.com/wiki/318/using-cjuidatepicker-for-cgridview-filter/
        // http://www.yiiframework.com/wiki/345/how-to-filter-cgridview-with-from-date-and-to-date-datepicker/
        // http://www.yiiframework.com/forum/index.php/topic/20941-filter-date-range-on-cgridview-toolbar/
        array(            
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
        ),
        array(            
            'name'=>'edit_date',
            'value' => 'str_replace(" ", "<br />", Yii::app()->dateFormatter->formatDateTime($data->edit_date))',
            'type' => 'raw',
            'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_edit_from', 
              'language' => 'ru',
            ), true)
            .
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_edit_to', 
              'language' => 'ru',
            ), true),
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
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$model = Post::model()->findByPk($this->crudid)->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	} 

  public function actionCategorie()
  {
	  $models = Categorie::model()->findAll(array(
	    'order'=>'sindex ASC'
	  ));
	  $this->render('dragndrop',array(
			'models'=>$models,
			'attSindex'=>'sindex',
			'attParent'=>'parent',
			'attId'=>'id',
			'attributes'=>array(
			  'name',
			),
		));
  }
	
	/**
	 * Создает или редактирует категорию
	 */
	public function actionCategorieUpdate()
	{
	  if (!empty($this->crudid) && $this->crud == 'update')
      $model=Categorie::model()->findByPk($this->crudid);
    else
      $model = new Categorie;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Categorie']))
		{
			$model->attributes=$_POST['Categorie'];
/*			
			if ($this->crud == 'create')
			{
			  if (!empty($this->crudid)) // Если задан id, значит это форма добавления подкатегории
			    $model->parent = $this->crudid;
			}
 */
			if(empty($model->parent)) $model->parent = 0; // Если родитель пустой, значит это категория верхнего уровня
			
			$valid = $model->save();
		}
		
		if($_POST['ajaxSubmit'])
    {
      $data = array(
        'action' => 'renewForm',
        'content' => $this->renderPartial('update',array(
          'model'=>$model,
        ), true),
      );

      //обновляем страницу
      if($valid)
        $data['content'] .= '<script> location.reload() </script>';
      
      echo json_encode($data, JSON_HEX_TAG);
      Yii::app()->end();
    }
    
		if(!$_POST['ajaxSubmit'])
      $this->renderPartial('update',array(
			  'model'=>$model,
		  ), false, true);
	}
	public function actionCategorieCreate()
	{
	  $this->actionCategorieUpdate();
	}
	
	/**
	 * Удаление категории
	 */
	public function actionCategorieDelete()
	{
		if(Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			$model = Categorie::model()->findByPk($this->crudid)->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}  	
	
	/**
	 * Меняет родителя категории
	 */
	public function actionCategorieSetparent()
	{
	}
	
	/**
	 * Меняет порядок отображения категорий
	 */
	public function actionCategorieSetsindex()
	{
	  if($_GET['ajax'])
	  {
	    // данные для сортировки
      $sindexOld = $_GET['sindexold'];  // старый индекс перемещенного элемента
      $sindexNew = $_GET['sindexnew'];  // новый индекс перемеещенного элемента
      $id = $_GET['id'];
      
      $delta = $sindexOld - $sindexNew;      
      $delta = ($delta < 0)?'-1':'+1';
      $smin = min($sindexOld, $sindexNew);
      $smax = max($sindexOld, $sindexNew);// throw new CHttpException(400,$smin.' '.$smax);exit();   
      
      if($delta < 0 && $smin == 0) $smin = 1; // предотвращаем ухождение sindex в минуса

      Yii::app()->db->createCommand()
        ->update(Categorie::model()->tableName(), array(
          'sindex'=>new CDbExpression('sindex'.$delta)
        ), 'sindex>=:smin AND sindex<=:smax', array(':smin'=>$smin, ':smax'=>$smax));

      Yii::app()->db->createCommand()
        ->update(Categorie::model()->tableName(), array(
          'sindex'=>$sindexNew
        ), 'id=:id', array(':id'=>$id));
	  }
	}

  /**
   *  @return array JSON массив с тегами для jQuery UI AutoComplete
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
