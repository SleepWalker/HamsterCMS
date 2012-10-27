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
  public $scriptAlias; // alias к папке с скриптами
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'meval.models.*',
			'meval.components.*',
		));
    
    $this->scriptAlias = Yii::app()->modules['meval']['params']['scriptsAlias'];
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Все скрипты',
      'run' => array(
        'name'  => 'Выполнение скрипта',
        'display' => 'whenActive',
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
		// список файлов в директории
	  $fileListOfDirectory = array();
    $pathTofileListDirectory = Yii::getPathOfAlias($this->scriptAlias);
    foreach( new DirectoryIterator($pathTofileListDirectory) as $file) {
        if( $file->isFile() === TRUE) {
            array_push($fileListOfDirectory, array(
              'name' => $file->getBasename(),
              //'size' => $file->getSize(),
              //'time' => $file->getMTime()
            ));
        }
    }
    
    $dataProvider=new CArrayDataProvider($fileListOfDirectory, array(
      'pagination'=>array(
          'pageSize'=>20,
      ),
      'sort'=>array(
        'attributes'=>array(
          'name',
        ),
        'defaultOrder'=>array(
          'name'=>CSort::SORT_ASC,
        )
      ),
    ));
    
    $this->render('table', array(
      'dataProvider'=>$dataProvider,
      'buttons' => array(
        'run'=>array(
          'url'=>'"/admin/meval/run/".substr($data["name"], 0, strpos($data["name"], "."))',
          'imageUrl'=> $this->adminAssetsUrl . '/images/icon_run.png',
        )
      ),
      'columns'=>array(
			  array(
          'name'=>'Имя скрипта',
          'value'=>'$data["name"]',
        ),
      ),
    ));
  }
  
  public function actionRun()
  {
    // Защита от мастира
    error_reporting(E_ALL ^ E_DEPRECATED);
    ob_start();
    require(Yii::getPathOfAlias($this->scriptAlias) . '/' . $this->crud . '.php');
    $this->renderText(ob_get_clean());
  }
} 
?>