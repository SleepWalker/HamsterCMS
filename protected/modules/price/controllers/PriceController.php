<?php
/**
 * PriceController class for price module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.price.controllers.PriceController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PriceController extends Controller
{
  //TODO: нужно сохранять прайсы в защищенной дириктории и выдавать их по экшену только необходимым людям
  public $layout='//layouts/column3';
	/**
	 * @return array action filters
	 */
	/*public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
  }*/
  
  /**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
  public function accessRules()
  {
    return array();
    /*return array(
      array('allow',
      'roles'=>array('price.access'),
    ),
    array('deny',  // deny all users
    'users'=>array('*'),
  ),
);*/
  }

  /**
   * Отображает прайсы и фильтрует их позиции на основе информации из GET запроса
   * 
   * @access public
   * @return void
   */
  public function actionIndex()
  {
    $config = HPrice::getConfig();

    $chooseInvitation = $priceChoises[''] = 'Не важно';
    // меню фильтрации по файлам прайсов
    foreach($config as $price)
      if(isset($price['id']))
      {
        $priceChoises[$price['id']] = $price['name'];

        // добываем список категорий
        foreach(array_keys($price['columns']['cat']) as $curCat)
        {
          if(!isset($cats[$curCat . '_id']))
          {
            if(isset($_GET['Price']['file_id']))
              $catsData = PriceCat::model($curCat)->findAllByAttributes(array('file_id' => (int)$_GET['Price']['file_id']));
            else
              $catsData = PriceCat::model($curCat)->findAll();

            $cats[$curCat . '_id'][''] = $chooseInvitation;
            foreach($catsData as $cat)
              $cats[$curCat . '_id'][$cat->primaryKey] = $cat->name;
          }
        }
      }
    
    $prod = new Price('search');
    if($_GET['Price'])
      $prod->attributes = $_GET['Price'];
      
    $dataProvider = $prod->search();


    $this->pushAside(array(
      'block_filter',
      'prod' => $prod,
      'cats' => $cats,
      ),
    array(
      'title' => 'Фильтр',
      'position' => 'top',
    ));

    // колонки для таблицы
    // TODO: перенести в модель всю инфу о колонках
    $columns =  array(
      'code',
      'name',
      'price',
    );

    foreach(array_keys($cats) as $cat)
      $columns[] = str_replace('_id', 'Name', $cat);


    // TODO: названия колонок должны генерироваться в модели
    if(is_array(($extra = $config['extraLabels'])))
      foreach($extra as $attribute => $name)
        $columns[] = array(
          'name' => $name,
          'header' => $name,
          'value' => '$data->extra["' . $attribute . '"]',
        );

    $priceTable = $this->widget('zii.widgets.grid.CGridView', array(
      'dataProvider'=>$dataProvider,
      'columns'=>$columns,
      'pager'=>array(
        'cssFile'=>false,
        'header'=>false,
        'maxButtonCount' => 8,
      ),
      'cssFile'=>false,
      'summaryText' => false,
      'enableHistory' => true,
    ), true);


    $priceDownloadMenu = array();
    // список прайсов
    foreach( new DirectoryIterator(HPrice::getPricePath()) as $file) {
      if( $file->isFile() === TRUE && substr($file->getBasename(), 0, 1) != '.') 
        $priceDownloadMenu[] = array(
          'name' => $file->getBasename(),
          'link' => HPrice::getPriceUrl() . '/' . $file->getBasename(),
        );
    }

		$this->render('index', array(
      'priceTable' => $priceTable,
      'priceDownloadMenu' => array(
        'elements' => $priceDownloadMenu,
      ),
    ));
	}
}
