<?php
/**
 * Admin action class for meval module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.meval.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{
  public $scriptAlias; // alias к папке с скриптами

  /**
   * @property string $runtimeDir путь к папке с файлами скриптов
   */
  public $runtimeDir;

  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'meval.models.*',
			'meval.components.*',
		));
    
    $this->scriptAlias = Yii::app()->modules['meval']['params']['scriptsAlias'];
    $this->runtimeDir = Yii::getPathOfAlias($this->scriptAlias . '.runtime');
    if(!is_dir($this->runtimeDir))
      mkdir($this->runtimeDir);
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
   *  Выводит таблицу с загруженными в папку {@link $scriptAlias} скриптами
   */
  public function actionIndex() 
  {
		// список файлов в директории
	  $fileListOfDirectory = array();
    $pathTofileListDirectory = Yii::getPathOfAlias($this->scriptAlias);
    if(is_dir($pathTofileListDirectory))
      foreach( new DirectoryIterator($pathTofileListDirectory) as $file) {
        if( $file->isFile() === TRUE && $file->getExtension() == 'php') {
          array_push($fileListOfDirectory, array(
            'name' => $file->getBasename('.php'),
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
          'url'=>'"/admin/meval/run/".$data["name"]',
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
    error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
    $this->runtimeDir .= '/'.$this->crud;
    if(!is_dir($this->runtimeDir))
      mkdir($this->runtimeDir);

    Yii::import($this->scriptAlias.'.inc.'.$this->crud.'.*');
    Yii::import($this->scriptAlias.'.inc.'.$this->crud.'.models.*');

    ob_start();
    require(Yii::getPathOfAlias($this->scriptAlias) . '/' . $this->crud . '.php');
    $this->renderText(ob_get_clean());
  }
} 
?>
