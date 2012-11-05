<?php
/**
 * Admin action class for shop module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.shop.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class AdminAction extends HAdminAction
{
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'shop.models.*',
			'shop.components.*',
		));
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все товары',
      'update'  => array(
        'name' => 'Редактирование товара',
        'display' => 'whenActive',
      ),
      'create'  => array(
        'name' => 'Добавить товар',
        'display' => 'index',
      ),
      'categorie'  => 'Управление категориями',
      'categorie/charshema'  => array(
        'name' => 'Редактирование характеристик',
        'display' => 'whenActive',
      ),
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
      ),
    );
  }
  
  public function renderDepDropDown($catId, $ddArr)
  {
    return CHtml::dropDownList('cat_id_'.$catId, $catId, $ddArr,
    array(
      'ajax' => array(
        'type'=>'POST', //request type
        'url'=>$this->actionPath, //url to call.
        'beforeSend' => 'startLoad',
        'complete' => 'stopLoad',
        'update'=>'#cat_id_update', //selector to update
        'data'=>'js:"catId="+$(this).val()',
      ),
      'class' => 'catDepDropDown',
      'empty' => '--Выберите категорию--',
    ));
  }
  
  /**
	 * Генерирует зависимые выпадающийе списки для выбора категории
	 */
  public function actionUpdateDdd()
  {
    if(Yii::app()->request->isPostRequest || 1)
    {
      $tree = Categorie::model()->getDDTree($_POST['catId']);
      
      // Рендерим зависимые выпадающие списки
      foreach($tree as $treeLevel)
        $output = $this->renderDepDropDown($treeLevel['id'], $treeLevel['items']) . '<p/>' . $output;
        
      // Отключаем jquery
      Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 
      
      // Рендерим скрипты
      Yii::app()->getClientScript()->render($output);
      echo $output;
    }
  }
  
  public function actionCreateDdd()
  {
    $this->actionUpdateDdd();
  }
  
  /**
	 * Генерирует таблицу с характеристиками в зависимости от ID категории
	 */
  public function actionUpdateChtbl()
  {
    if(Yii::app()->request->isPostRequest)
    {
      // Добываем id родителей текущей категории
      $catIds = Categorie::model()->getParentsCatIds((int)$_POST['catId'], array((int)$_POST['catId']));
      
      $criteria = new CDbCriteria;

      $criteria->addInCondition('cat_id', $catIds);

      if($_POST['prodId'])
      {
        $charModels = Char::model()->findAllByAttributes(array('prod_id'=>$_POST['prodId']));
        // создаем массив char_id=>char_value
        foreach($charModels as $charModel)
        {
          $charValues[$charModel->char_id] = $charModel->char_value;
        }
      }

      $charShemas = CharShema::model()->findAll($criteria);
      $this->renderPartial('shop.views.admin.chtbl', array(
        'charShemas' => $charShemas,
        'charValues' => $charValues,
      ));
    }
  }
  public function actionCreateChtbl()
  {
    $this->actionUpdateChtbl();
  }

  /**
	 * Создает или редактирует модель
	 */
  public function actionUpdate() 
  {
    $uploadPath = $_SERVER['DOCUMENT_ROOT'].Shop::$uploadsUrl;
	  if(!is_dir($uploadPath)) // создаем директорию для картинок
	    mkdir($uploadPath, 0777);
	    
    //JS для обработки зависимых выпадающих списков выбора категории и подгрузки полей характеристик
    $this->registerFormUpdScript();

    if ($this->crudid)
      $model=Shop::model()->findByPk($this->crudid);
    else
      $model = new Shop;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
      $modelsToValidate[] = $model;
      // проверяем обязательность характеристик
      if($_POST['Shop']['status'] != STATUS_DRAFT) // для статуса черновик не проводим валидацию
      {
        if(isset($_POST['Char']))
        {
          $charShemas = CharShema::model()->findAllByAttributes(array('char_id'=>array_keys($_POST['Char'])));
          
          foreach($charShemas as $charShema)
            $charShemaById[$charShema->char_id] = $charShema;
          
          foreach($_POST['Char'] as $charId => $charData)
          {
            if(!$charShemaById[$charId]->isRequired) continue; // не обязатльные категории пропускаем
            $mChar = new Char('validate');
            $mChar->attributes = $charData;
            $mChar->char_id = $charId;
            $mChar->type = $charShemaById[$charId]->type;
            $modelsToValidate[] = $mChar;
          }
        }
      }
      echo CActiveForm::validate($modelsToValidate);
			Yii::app()->end();
		}

		if(isset($_POST['Shop']))
		{

			$model->attributes=$_POST['Shop'];
			
			//$oldImage = $model->brand_logo; // сохраняем старую картинку, которую, возможно, надо будет удалить в случае успешной валидации формы	
      
      //if ($_POST['Brand']['uImage'] == 'delete') $model->brand_logo = ''; // Удаляем инфу о файле из БД, так как файл помечен на удаление, а нового в замен нету.
			$model->uImage=CUploadedFile::getInstances($model,'uImage');	

      if ($model->validate()) 
      {//throw new CHttpException(404,'Ошибка валидации');
        
        // Проверяем не удалили ли какие-то из уже загруженных файлов
        if (is_array($_POST['delFile']))
		      foreach($model->photo as $i=>$imgName)
		        if(in_array($imgName, $_POST['delFile'])) // Файл помечен на удаление. Удаляем его
		          if(file_exists($uploadPath.$imgName)) 
		          {
		            unlink($uploadPath.$imgName);
		            unset($model->photo[$i]);
		          }
			
			  if ($model->uImage) // Если были загружены изображения
			  {			  
			    foreach($model->uImage as $img)
			    {
			      // Если файл помечен был помечен на удаление. Не обрабатываем его.
			      if(is_array($_POST['delFile']) && in_array($img->getName(), $_POST['delFile'])) continue;
			      
				    //$sourcePath = pathinfo($img->getName());//.$sourcePath['extension'];
				    $fileName = $model->page_alias.'_'.uniqid().'.jpg';
				
				    
		      	//$file - путь и имя, куда сохранится картинка без изменений
				    $file = $uploadPath.$fileName;
				
				    // Ресайзим загруженное изображение
				    Yii::import('application.vendors.wideImage.WideImage');
				    $wideImage = WideImage::load($img->tempName);
            $white = $wideImage->allocateColor(255, 255, 255);
            $wideImage->resize(600, 500)->resizeCanvas(600, 500, 'center', 'center', $white);

            // watermark image
            $watermarkPath = array(Yii::app()->theme->viewPath, 'shop', 'watermark.png');
            $watermarkPath = implode(DIRECTORY_SEPARATOR, $watermarkPath);
            if(file_exists($watermarkPath))
            {
              $watermark = WideImage::load($watermarkPath);
              $wideImage = $wideImage->merge($watermark, 'right - 30', 'bottom - 30');
            }
            
            $wideImage->saveToFile($file, 75);
				    
				    $model->photo[] = $fileName; // Сохраняем инфу о файле в бд
				  }
			  }
			
			  if($model->save(false))
			  {
			    // Обрабатываем поля характеристик		
			    if($_POST['Char'])	
			    {
  			    foreach($_POST['Char'] as $charId => $charData)
  			    {
              if(empty($charData['char_value'])) 
              {
                // пустые характеристики удаляем из пост массива (потом их удалит и из бд)
                unset($_POST['Char'][$charId]);
                continue; 
              }
  			      $charModel = Char::model()->findByAttributes(array('char_id'=>$charId, 'prod_id'=>$model->id));
  			      if(!$charModel) $charModel = new Char();
  			      $charModel->prod_id = $model->id;
  			      $charModel->char_id = $charId;
  			      
  			      /*if(is_array($charValue)) $charValue = implode('; ', $charValue);*/
  			      $charModel->char_value = $charData['char_value'];
  			      
  			      // сейвим все поочереди
  			      if(!$charModel->save())
  			        throw new CHttpException(404,'Ошибка при сохранении');    
  			    }
  			    
  		      // Зачищаем старые характеристики (только в случае редактирования)
  		      if ($this->crudid && count($_POST['Char']))
              Char::model()->deleteAllByAttributes(array(
                  'prod_id' => $model->id,
                ),
                'char_id NOT IN(' . implode(', ', array_keys($_POST['Char'])) . ')'
              );
          }
          
          $saved = true;
			  }
			}
			//else
			//  throw new CHttpException(404,'Ошибка при сохранении');
		}
		
		if($_POST['ajaxIframe'])
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
	 * JS для обработки зависимых выпадающих списков,
	 * выбора категории и подгрузки полей характеристик
	 */
  function registerFormUpdScript()
  {
    $prodId = ($this->crudid)?$this->crudid:'0';
    $catUpdInitJs = '
	    var catId = $("#Shop_cat_id").val(); // Выбранная категория 
	    // контейнер для выпадающих списков
	    $("<div id=\"cat_id_update\"></div>").insertAfter($("#Shop_cat_id"));
	    jQuery.ajax("' . $this->actionPath . 'ddd", {
	      type: "POST",
	      data: {catId:catId},
	      success: function (answer)
	      {
	        $("#cat_id_update").html(answer);
	      }
	    });
	    
	    var prodId = ' . $prodId . ';
	    
	    var renewRelatedRows = function(charIds, show, isSelect)
	    {
	      show = (show)?"show":"hide";
	      if(isSelect != undefined) // для селектов
	      {
  	      for (i in charIds)
            for (j in charIds[i]) 
            {
              if (isSelect == i) continue; // пропускаем итерацию, в которой находится активные характеристики
              $("#char_update .char"+charIds[i][j]).hide();
              clearValues($("#char_update .char"+charIds[i][j]));
            }
          charIds = charIds[isSelect]; // выбрали массив, соответствующий активному пункту выпадающего списка
          show = "show";
        }
        for (j in charIds) 
        {
          $("#char_update .char"+charIds[j])[show]();
          if(show == "hide")
          {
            clearValues($("#char_update .char"+charIds[j]));
          }
        }
	    };
      
      // Очищаем значения в форме
      function clearValues(obj)
      {
        obj.find("input[type=text]")
        .add(obj.find("select"))
        .val("");
        obj.find("input:checked").removeProp("checked");
      }
	    
	    // Обновляем таблицу характеристик Char
	    var renewChar = function(catId)
	    {
	      jQuery.ajax("' . $this->actionPath . 'chtbl", {
	        type: "POST",
	        data: {catId:catId, prodId:' . $prodId . '},
	        success: function (answer)
	        {
	          $("#char_update").html(answer);
	          // Обработка зависимых характеристик
	          $("#char_update .relatedChar").each(function(index) {
	            var charIds = jQuery.parseJSON( $(this).attr("relChar") );

	            var selectedIndex = $(this).prop("id");
        	    selectedIndex = ($(this).is("[type=checkbox]")) ? selectedIndex.slice(selectedIndex.lastIndexOf("_") + 1) : $(this)[0].selectedIndex - 1;

	            // Прячем все категории, которые были помечены как зависимые
	            if($(this).is("[type=checkbox]"))
	            {
	              $(this).change(function() {
  	              var selectedIndex = $(this).prop("id");
          	      selectedIndex = selectedIndex.slice(selectedIndex.lastIndexOf("_") + 1);
                  renewRelatedRows(charIds[selectedIndex], $(this).is(":checked")); // показываем зависимые характеристики
  	            });
	              renewRelatedRows(charIds[selectedIndex], $(this).is(":checked"));
	            }
	            else
	            { 
	              $(this).change(function() {
          	      var selectedIndex = $(this)[0].selectedIndex - 1;console.log(selectedIndex);
                  renewRelatedRows(charIds, null, selectedIndex);
  	            });
	              renewRelatedRows(charIds, null, selectedIndex);
	            }
	          });
	        },
	        error:function(xhr, textStatus, errorThrown){console.log(xhr.responseText)},
	        cache: false,
	      });
	    };
      renewChar(catId);
      
      // Обработчик события валидации для полей характеристик
	    $("body").on("afterValidate", function(event, form, data, hasError) {
        var hasErrors = false; // индикатор наличия ошибок при валидации
        // зачищаем классы ошибок, перед тем, как присваивать новые ошибки
        $("#charGrid .row").removeClass("error").addClass("success");
        $("#charGrid .errorMessage").html("");
        for(var att in data)
        {
          // проверяем, является ли этот атрибут полем значения категории
          // так же добавляем в условие проверку на видимость строки таблицы
          // (нужно в случае зависимых характеристик. 
          // те, которые не видимые - не должны быть заполненными, не зависимо от их типа
          // эта фильтрация происходит только на стороне клиента, тоесть в данном случае мы игнорим сообщения от сервера о незаполненном поле, если оно скрытое)
          if(att.indexOf("char_value") != -1 && $("#"+att+"_em_").parents("tr").is(":visible"))
          {
            hasErrors = true;
            var errorString = data[att].toString();
            $("#"+att+"_em_").text(errorString).show();
            $("#"+att+"_em_").parents(".row").removeClass("success").addClass("error");
          }
        }
        
        return hasErrors;
      });
	    
	    
	    // Задаем событие, которое будет устанавливать значение в input с cat_id
	    // Задаем событие, которое будет обновлять таблицу Char при смене категории
	    $("form").on("change", "form .catDepDropDown", function()
	    {
	      $("#Shop_cat_id").val( $(this).val() );
	      renewChar( $(this).val() );
	    });
	  ';
	  Yii::app()->getClientScript()->registerScript('dependentDropDown', $catUpdInitJs);
  }
  
  /**
   *  Выводит таблицу всех товаров
   */
  public function actionIndex() 
  {
    $model=new Shop('search');
    $model->unsetAttributes();
    if(isset($_GET['Shop']))
      $model->attributes=$_GET['Shop'];
	  
		$this->render('table',array(
			'dataProvider'=> $model->latest()->search(),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
			  'id',
			  array(            
            'name'=>'photo',
            'value'=>'count($data->photo) ? $data->img(45) : ""',
            'type'=>'raw',
            'filter'=>'',
        ),
        'product_name', 
        array(            
            'name'=>'cat_search',
            'value' => '$data->cat->cat_name',
        ),
        array(            
            'name'=>'brand_search',
            'value' => '$data->brand->brand_name',
        ),
        array(            
            'name'=>'price',
            'type'=>'raw',
            'value' => 'CHtml::activeTextField($data, "price", array("size"=>7, "style"=>"width:auto"), array("ajax" => array(
                "type" => "POST",
                "url" => "asdf",
            )))',
            'filter'=>'',
        ),
        array(            
            'name'=>'status',
            'type'=>'raw',
            'value' => '$data->statusName',
            'filter'=> Shop::getStatusNames(),
        ),
        'rating',
        array(            
            'name'=>'user_search',
            'value' => '$data->user->first_name',
        ),
        array(            
            'name'=>'supplier_search',
            'value' => '$data->supplier->name',
            'filter' => Supplier::model()->suppliersList,
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
              'language' => Yii::app()->language,
              /*'htmlOptions' => array(
                'id' => 'datepicker_for_due_date',
                'size' => '10',
              ),*/
              'htmlOptions' => array('class'=>'reinstallDatePicker'),
              'defaultOptions' => array(  
                'showOn' => 'focus', 
                'showOtherMonths' => true,
                'selectOtherMonths' => true,
                'changeMonth' => true,
                'changeYear' => true,
                'showButtonPanel' => true,
                'autoSize' => true,
                'dateFormat' => "yy-mm-dd",
              ),
            ), true)
            .
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_add_to', 
              'language' => Yii::app()->language,
              'htmlOptions' => array('class'=>'reinstallDatePicker'),
            ), true),
        ),
        array(            
            'name'=>'edit_date',
            'value' => 'str_replace(" ", "<br />", Yii::app()->dateFormatter->formatDateTime($data->edit_date))',
            'type' => 'raw',
            'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_edit_from', 
              'language' => Yii::app()->language,
              'htmlOptions' => array('class'=>'reinstallDatePicker'),
            ), true)
            .
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $model, 
              'attribute'=>'date_edit_to', 
              'language' => Yii::app()->language,
              'htmlOptions' => array('class'=>'reinstallDatePicker'),
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
		if(Yii::app()->request->isPostRequest && Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			Shop::model()->findByPk($this->crudid)->delete();
		}
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}  
	
	/**
	 * Выводит список брендов
	 */
	public function actionBrand()
	{
	  $dataProvider=new CActiveDataProvider('Brand', array(
	      'pagination' => array(
          'pageSize'=>Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']),
        ),
	    )
	  );
		$this->render('table',array(
			'dataProvider'=>$dataProvider,
			'columns'=>array(
			  'brand_name',
        array(           
          'name'=>'brand_logo',
          'value'=>'CHtml::image(Brand::$uploadsUrl . $data->brand_logo, $data->brand_name, array("style"=>"width:50px;"))',
          'type'=>'html',
        ),
      ),
		));
	}
	
	/**
	 * Создает или редактирует бренд
	 */
	public function actionBrandUpdate()
	{
	  $uploadPath = $_SERVER['DOCUMENT_ROOT'].Brand::$uploadsUrl;
	  if(!is_dir($uploadPath)) // создаем директорию для картинок
	    mkdir($uploadPath, 0777);
	    
	  if (!empty($this->crudid))
      $model=Brand::model()->findByPk($this->crudid);
    else
      $model = new Brand;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}//if($_POST['ajaxSubmit']){throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');}

		if(isset($_POST['Brand']))
		{
			$model->attributes=$_POST['Brand'];
			
			$oldImage = $model->brand_logo; // сохраняем старую картинку, которую, возможно, надо будет удалить в случае успешной валидации формы	
      
      if ($_POST['Brand']['uImage'] == 'delete') $model->brand_logo = ''; // Удаляем инфу о файле из БД, так как файл помечен на удаление, а нового в замен нету.
			$model->uImage=CUploadedFile::getInstance($model,'uImage');		
			
			if ($model->uImage)
			{
				$sourcePath = pathinfo($model->uImage->getName());
				$fileName = $model->brand_alias.'_'.uniqid().'.png';//.$sourcePath['extension'];
				$model->brand_logo = $fileName;
			}

			if($model->save()) 
			{ //throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
			  // Удаляем старое изображение
			  if($oldImage != '' && file_exists($uploadPath.$oldImage) && ($fileName != '' || $model->brand_logo == '')) unlink($uploadPath.$oldImage);
			  
			//Если поле загрузки файла не было пустым, то          
				if ($model->uImage)
				{				  				
					$file = $uploadPath.$fileName; //Переменной $file присвоить путь, куда сохранится картинка без изменений
					//$model->uImage->saveAs($file);
					
					// Ресайзим загруженное изображение
					Yii::import('application.vendors.wideImage.WideImage');
					WideImage::load($model->uImage->tempName)->resize(100, 100)->resizeCanvas(100, 100, 'center', 'center')->saveToFile($file, 9);
        }
      }
      else
			  throw new CHttpException(404,'Ошибка при сохранении');
		}
		
		$model->uImage = $model->brand_logo;
		
		if($_POST['ajaxIframe'])
    {
      $data = array(
        'action' => 'renewForm',
        'content' => $this->renderPartial('update',array(
	                     'model'=>$model,
                     ), true),
      );
      
      echo json_encode($data, JSON_HEX_TAG);
      Yii::app()->end();
    }
		
		if(!$_POST['ajaxSubmit'])
      $this->render('update',array(
			  'model'=>$model,
		  ));
	}
	
	public function actionBrandCreate()
	{
	  $this->actionBrandUpdate();
	}
	
	/**
	 * Удаление бренда
	 */
	public function actionBrandDelete()
	{
	  $uploadPath = $_SERVER['DOCUMENT_ROOT'].Brand::$uploadsUrl;
		if(Yii::app()->request->isPostRequest && Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			$model = Brand::model()->findByPk($this->crudid);
			// Удаляем изображение
		  if(file_exists($uploadPath.$model->brand_logo)) unlink($uploadPath.$model->brand_logo);
			$model->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}  
	
	/**
	 * Выводит список категорий
	 */
	public function actionCategorie()
	{
	  $models = Categorie::model()->findAll(array(
	    'order'=>'cat_sindex ASC'
	  ));
	  $this->render('dragndrop',array(
			'models'=>$models,
			'attSindex'=>'cat_sindex',
			'attParent'=>'cat_parent',
			'attId'=>'cat_id',
			'attributes'=>array(
			  'cat_name',
			),
		));
	}
	
	/**
	 * Создает или редактирует категорию
	 */
	public function actionCategorieUpdate()
	{
	  $uploadPath = $_SERVER['DOCUMENT_ROOT'].Categorie::$uploadsUrl;
	  if(!is_dir($uploadPath)) // создаем директорию для картинок
	    mkdir($uploadPath, 0777);
	    
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
			
			if ($this->crud == 'create')
			{
			  if (!empty($this->crudid)) // Если задан id, значит это форма добавления подкатегории
			    $model->cat_parent = $this->crudid;
			}
			  
			if(empty($model->cat_parent)) $model->cat_parent = 0; // Если родитель пустой, значит это категория верхнего уровня
			
			$oldImage = $model->cat_logo; // сохраняем старую картинку, которую, возможно, надо будет удалить в случае успешной валидации формы	
      
      if ($_POST['Categorie']['uImage'] == 'delete') $model->cat_logo = ''; // Удаляем инфу о файле из БД, так как файл помечен на удаление, а нового в замен нету.
			$model->uImage=CUploadedFile::getInstance($model,'uImage');		
			if ($model->uImage)
			{
				$sourcePath = pathinfo($model->uImage->getName());
				$fileName = $model->cat_alias.'_'.uniqid().'.png';//.$sourcePath['extension'];
				$model->cat_logo = $fileName;
			}

			if($model->save()) { 
			  // Удаляем старое изображение
			  if($oldImage != '' && file_exists($uploadPath.$oldImage) && ($fileName != '' || $model->cat_logo == '')) unlink($uploadPath.$oldImage);
			  
			//Если поле загрузки файла не было пустым, то          
				if ($model->uImage){				  				
					$file = $uploadPath.$fileName; //Переменной $file присвоить путь, куда сохранится картинка без изменений
					//$model->uImage->saveAs($file);
					
					// Ресайзим загруженное изображение
					Yii::import('application.vendors.wideImage.WideImage');
					WideImage::load($model->uImage->tempName)->resize(100, 100)->resizeCanvas(100, 100, 'center', 'center')->saveToFile($file, 9);
        }
      }
      else 
			  throw new CHttpException(404,'Ошибка при сохранении');
		}
		
		$model->uImage = $model->cat_logo;
		
		if($_POST['ajaxIframe'])
    {
      $data = array(
        'action' => 'renewForm',
        'content' => $this->renderPartial('update',array(
	                     'model'=>$model,
                     ), true),
      );
      
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
	  $uploadPath = $_SERVER['DOCUMENT_ROOT'].Categorie::$uploadsUrl;
		if(Yii::app()->request->isPostRequest && Yii::app()->user->checkAccess('admin'))
		{
			// we only allow deletion via POST request
			$model = Categorie::model()->findByPk($this->crudid);
			// Удаляем изображение
		  if(file_exists($uploadPath.$model->cat_logo)) unlink($uploadPath.$model->cat_logo);
			$model->delete();
	  }
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}  	
	
	/**
	 * Управление набором характеристик
	 */
	public function actionCategorieCharshema()
	{
	  if(empty($this->crudid)) return false;
	  
	  $parentIds = Categorie::model()->getParentsCatIds($this->crudid);
	  if(count($parentIds))
	  {
	    $header = '<h1>Родительские характеристики</h1>';
	    foreach($parentIds as $pCatId)
	    {
	      $pCatCSchema = CharShema::model()->findAllByCat($pCatId);
	      foreach($pCatCSchema as $pCatChar)
	        $header .= $pCatChar->char_name.'; ';
	    }
	  }
	  
	  $models = CharShema::model()->findAllByCat($this->crudid);
    // пересчитываем все модели в массив char_id=>charModel
    foreach($models as $modelItem)
      $modelByCharId[$modelItem->char_id] = $modelItem;
    
	  if(!count($models)) 
	    $models = array(new CharShema);
	  $valid = true;
	  if(isset($_POST['CharShema']))
	  {
      foreach($_POST['CharShema'] as $i=>$item)
      {
        $curModel = $modelByCharId[$item['char_id']];
        // первые $i моделей - это уже существующие записи в бд, некоторые из них могли быть удалены во время редактирования
        if(isset($item['char_id']) && count($item) < 2) // это поле удалено, так как от него остался только id
        {
          $curModel->delete();
          continue;
        }
        elseif (!isset($item['char_id'])) //это новое поле
        {
          $curModel = new CharShema;
          $curModel->cat_id = (int)$this->crudid;
        }
        // загружаем данные в модели новых полей и в модели полей, которые редактируются
        $curModel->attributes = $item;
        $valid = $curModel->save() && $valid;
        // здесь мы создаем новый массив, в котором находятся актуальные модели для рендеринга
        $models4render[] = $curModel;
      }
      if(!$valid)
        Yii::trace('Ошибка при редактировании схемы характеристик', 'CharShemaEdit');
            
      if(is_array($models4render))
        $models = $models4render;
      
      // Отключаем jquery для ajax Ответов
      Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 
      $data = array(
          'action' => 'renewForm',
          /*'content' => $this->renderPartial('shop.views.admin.batchUpdate',array(
              'model'=>$models,
              'attributes'=>array(
                'char_name',
                'char_suff',
                'type',
              ),
              'pk'=> 'char_id',
           ), true, true),*/
        );
        
        echo json_encode($data, JSON_HEX_TAG);
        Yii::app()->end();
	  }
	  
	  
	  if(!$_POST['ajaxSubmit'])
      $this->render('shop.views.admin.batchUpdate',array(
        'model'=>$models,
		    'header'=>$header,
	    ));
  }
	
	/**
	 * Меняет родителя категории
	 */
	public function actionCategorieSetparent()
	{
	  if($_GET['ajax'])
	  {
	    $model = Categorie::model()->findByPk($_GET['id']);
	    $model->cat_parent = $_GET['parentid'];
	    $model->save();
	  }
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
          'cat_sindex'=>new CDbExpression('cat_sindex'.$delta)
        ), 'cat_sindex>=:smin AND cat_sindex<=:smax', array(':smin'=>$smin, ':smax'=>$smax));

      Yii::app()->db->createCommand()
        ->update(Categorie::model()->tableName(), array(
          'cat_sindex'=>$sindexNew
        ), 'cat_id=:id', array(':id'=>$id));
	  }
	}
	
	/**
	 * Выводит список поставщиков
	 */
	function actionSuppliers()
	{
	  $model=new Supplier('search');
    $model->unsetAttributes();
    if(isset($_GET['Supplier']))
            $model->attributes=$_GET['Supplier'];
	  
		$this->render('table',array(
			'dataProvider'=> $model->search(),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
			  'code',
        'name', 
      ),
		));
	}
	
	/**
	 * Добавление/редактирование поставщиков
	 */
	function actionSuppliersUpdate()
	{
	  if (!empty($this->crudid) && $this->crud == 'update')
      $model=Supplier::model()->findByPk($this->crudid);
    else
      $model = new Supplier;
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Supplier']))
		{
			$model->attributes=$_POST['Supplier'];
			

			if(!$model->save())
			  throw new CHttpException(404,'Ошибка при сохранении');
		}
		
		
		if(!$_POST['ajaxSubmit'])
      $this->render('update',array(
			  'model'=>$model,
		  ));
	}
	
	function actionSuppliersCreate()
	{
	  $this->actionSuppliersUpdate();
	}
} 
?>
