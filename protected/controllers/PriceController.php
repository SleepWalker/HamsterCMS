<?php
/**
 * Controller class for siplaying and managing price table
 * Uses xls widget
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('application.widgets.xls.models.*'); 
Yii::import('shop.widgets.filter.FilterRangeSlider'); 
class PriceController extends Controller
{
  public $layout='//layouts/column3';
  public $asideTop;
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}
  
  public function accessRules()
  {
      return array(
          array('allow',
              'roles'=>array('admin'),
          ),
          array('allow',  
            'actions'=>array('parts'),
            'users'=>array('*'),
          ),
          array('deny',  // deny all users
    				'users'=>array('*'),
    			),
      );
  }

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
    //T!: кеширование    
    // добываем список брендов
    $brandsData = XlsBrand::model()->findAll();
    $brands[''] = $cats[''] = 'Не важно';
    foreach($brandsData as $brand)
      $brands[$brand->id] = $brand->name;
    
    // добываем список категорий
    $catsData = XlsCategorie::model()->findAll();
    foreach($catsData as $cat)
      $cats[$cat->id] = $cat->name;
    
    $prod = new XlsProd('search');
    if($_GET['XlsProd'])
      $prod->attributes = $_GET['XlsProd'];
      
    $dataProvider = $prod->search();
    
		$this->render('priceTable', array(
      'brands' => $brands,
      'cats' => $cats,
      'prod' => $prod,
      'dataProvider' => $dataProvider,
    ));
	}
  
  /**
   *  Генерирует простенькую страничку для управления парсингом прайсов
   */
  public function actionGen()
	{           
    // список файлов в директории
    foreach( new DirectoryIterator('priceImport') as $file) {
        if( $file->isFile() === TRUE && substr($file->getBasename(), 0, 1) != '.' && !strpos($file->getBasename(), '.log') && strpos($file->getBasename(), '.csv')) 
          $files[$file->getBasename()] = $file->getBasename();
    }
    $this->pageTitle = 'Управление прайсами';
    $this->breadcrumbs = array(
      'Прайсы' => '/price',
      $this->pageTitle,
    );
    ob_start();
    ?>
    <h1><?php echo $this->pageTitle; ?></h1>
    <form action="/price/gen" method="get" style="display:inline-block;text-align:center;">
    <?php
    echo '<p><label for="file">Файл прайсов:</label>' . CHtml::dropDownList('file', $_GET['file'], $files, array('style'=>'width:100%')) . '</p><br />';
    echo CHtml::submitButton('Сканировать', array('name'=>'parse'));
    echo CHtml::submitButton('Показать', array('name'=>'show'));
    echo CHtml::submitButton('Импортировать в БД', array('name'=>'import'));
    echo CHtml::submitButton('Показать отчет', array('name'=>'log'));
    echo CHtml::submitButton('Сбросить БД', array('name'=>'flush'));
    echo '';
    ?>
    </form>
    <p>
      <ul>
        <li><b>Сканировать</b> - сканирует выбранный файл и сохраняет информацию о нем</li>
        <li><b>Показать</b> - показывает в виде таблицы содержимое ранее отсканированного файла</li>
        <li><b>Импортировать в БД</b> - импортирует в базу данных информацию из отсканированого файла</li>
        <li><b>Показать отчет</b> - показывает отчет о результах последнего импорта файла</li>
        <li><b>Сбросить БД</b> - полностью очищает БД. (использовать, когда нужно обновить прайсы в базе данных)</li>
      </ul>
    </p>
    <div style="overflow:auto; height:500px;">
    <?php 
    $this->widget('application.widgets.xls.XlsWidget'); // включаем в буффер результаты работы виджета
    echo '</div>';
    $this->renderText(ob_get_clean());
	}
	
	public function actionShow()
	{
	  
	}
}
