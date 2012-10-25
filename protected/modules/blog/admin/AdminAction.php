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
      /*'categorie'  => 'Управление категориями',
      'brand' => 'Управление брендами',
      'brand/update'  => array(
        'name' => 'Редактирование бренда',
        'display' => 'whenActive',
      ),
      'brand/create'  => array(
        'name' => 'Добавить бренд',
        'display' => 'brand',
      ),
      'suppliers'  => 'Поставщики',
      'suppliers/update'  => array(
        'name' => 'Редактирование поставщика',
        'display' => 'whenActive',
      ),
      'suppliers/create'  => array(
        'name' => 'Добавить поставщика',
        'display' => 'suppliers',
      ),*/
    );
  }
  
  

  /**
	 * Создает или редактирует модель
	 */
  public function actionUpdate() 
  {
    $uploadPath = $_SERVER['DOCUMENT_ROOT'].Post::uploadsUrl;
	  if(!is_dir($uploadPath)) // создаем директорию для картинок
	    mkdir($uploadPath, 0777);
	  
    if ($this->controller->crudid)
      $model=Post::model()->findByPk($this->controller->crudid);
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
      
			$oldImage = $model->image; // сохраняем старую картинку, которую, возможно, надо будет удалить в случае успешной валидации формы	
      
      if ($_POST['Post']['uImage'] == 'delete') $model->image = ''; // Удаляем инфу о файле из БД, так как файл помечен на удаление, а нового в замен нету.
			$model->uImage=CUploadedFile::getInstance($model,'uImage');		
			if ($model->uImage)
			{
				$sourcePath = pathinfo($model->uImage->getName());
				$fileName = $model->alias.'_'.uniqid().'.jpg';
				$model->image = $fileName;
			}

      if ($model->validate()) 
      {//throw new CHttpException(404,'Ошибка валидации');
      
        // Проверяем не удалили ли старое изображение
			  if($oldImage != '' && file_exists($uploadPath.$oldImage) && ($fileName != '' || $model->image == '')) unlink($uploadPath.$oldImage);
			
			  //Если поле загрузки файла не было пустым, то          
				if ($model->uImage) {				  				
					$file = $uploadPath.$fileName; //Переменной $file присвоить путь, куда сохранится картинка без изменений
					
					// Ресайзим загруженное изображение
					Yii::import('application.vendors.wideImage.WideImage');
					$wideImage = WideImage::load($model->uImage->tempName);
          $white = $wideImage->allocateColor(255, 255, 255);
          
          $wideImage->resize(100, 90)
          ->resizeCanvas(100, 90, 'center', 'center', $white)
          ->saveToFile($file, 75);	    
			  }
			
			  if($model->save())
			  {
          $saved = true;
			  }
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
    $this->controller->aside['Теги'] = $tagsMenu;
	  
		$this->controller->render('table',array(
			'dataProvider'=> $model->latest()->search(),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
			  array(            
            'name'=>'image',
            'value'=>'$data->img(45)',
            'type'=>'raw',
            'filter'=>'',
        ),
        'title',
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
            'filter' => $this->controller->widget('zii.widgets.jui.CJuiDatePicker', array(
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
            $this->controller->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_add_to', 
              'language' => 'ru',
            ), true),
        ),
        array(            
            'name'=>'edit_date',
            'value' => 'str_replace(" ", "<br />", Yii::app()->dateFormatter->formatDateTime($data->edit_date))',
            'type' => 'raw',
            'filter' => $this->controller->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_edit_from', 
              'language' => 'ru',
            ), true)
            .
            $this->controller->widget('zii.widgets.jui.CJuiDatePicker', array(
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
		$uploadPath = $_SERVER['DOCUMENT_ROOT'].Post::uploadsUrl;
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$model = Post::model()->findByPk($this->controller->crudid);
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