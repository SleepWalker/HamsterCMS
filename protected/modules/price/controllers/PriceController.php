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
  //TODO: можно сохранять прайсы в защищенной дириктории и выдавать их по экшену только необходимым людям
  public $layout='//layouts/column3';
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
  }
  
  /**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
  public function accessRules()
  {
    return array(
      array('allow',
      'roles'=>array('price.access'),
    ),
    array('deny',  // deny all users
    'users'=>array('*'),
  ),
);
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

    $choseInvitation = $priceChoises[''] = 'Не важно';
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
            $catsData = PriceCat::model($curCat)->findAll();
            $label = isset($config['attributeLabels'][$curCat]) 
              ? $config['attributeLabels'][$curCat]
              : 'Категория';
            $cats[$curCat . '_id'][''] = $choseInvitation;
            foreach($catsData as $cat)
              $cats[$curCat . '_id'][$cat->primaryKey] = $cat->name;
          }
        }
      }

    $priceDownloadMenu = array();
    // список файлов в директории
    foreach( new DirectoryIterator(HPrice::getPricePath()) as $file) {
      if( $file->isFile() === TRUE && substr($file->getBasename(), 0, 1) != '.') 
        $priceDownloadMenu[HPrice::getPriceUrl() . '/' . $file->getBasename()] = $file->getBasename();
    }
    
    $prod = new Price('search');
    if($_GET['Price'])
      $prod->attributes = $_GET['Price'];
      
    $dataProvider = $prod->search();
    
		$this->render('index', array(
      'brands' => $brands,
      'cats' => $cats,
      'prod' => $prod,
      'config' => $config,
      'priceChoises' => $priceChoises,
      'dataProvider' => $dataProvider,
      'priceDownloadMenu' => $priceDownloadMenu,
    ));
	}
}
