<?php
/**
 * Controller class for shop module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.controllers.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ShopController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column3';
	public $_curCat;

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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index', 'view', 'brand', 'categorie', 'compare', 'search'),
				'users'=>array('*'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'roles'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
	  $this->layout='//layouts/column2';
    $this->render('view',array(
      'model'=>$this->loadModel($id),
    ));
	}
	
	/**
	 * Выводит результаты поиска по запросу $_GET['query']
	 */
	public function actionSearch($query)
	{
	  //T!:поиск должен быть через модель, иначе не будут работать правила валидации
	  $criteria=new CDbCriteria;
		$criteria->compare('code',$query,true, 'OR');
		$criteria->compare('page_alias',$query,true, 'OR');
		$criteria->compare('description',$query,true, 'OR');
		//$criteria->compare('cat_id',$this->cat_id);
		//$criteria->compare('brand_id',$this->brand_id);
		$criteria->compare('page_title',$query,true, 'OR');
		$criteria->compare('page_alias',$query,true, 'OR');
		$criteria->compare('description',$query,true, 'OR');
		//$criteria->compare('cat_id',$this->cat_id);
		//$criteria->compare('brand_id',$this->brand_id);
		$criteria->compare('product_name',$query,true, 'OR');

		$dataProvider=new CActiveDataProvider(Shop::model()->published(), array(
	    'criteria'=>$criteria,
	  ));
	  
	  $this->render('index',array(
		  'dataProvider'=>$dataProvider,
		  'itemView'=>'_view',
		  'title' => 'Поиск',
	  ));
	}
	
	/**
	 * Выводит список категорий или товары, пренадлежащие определенной категории.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionCategorie($alias='')
	{
	  if($alias == '') // страница /shop/categorie не доступна для просмотра
	    throw new CHttpException(404,'Запрашиваемая страница не найдена');

    try
	  { 
  	  // Проверяем является ли категория родителем
  	  $sql = 'SELECT count(*) FROM ' . Categorie::model()->tableName() . ' WHERE cat_parent='.$this->curCat->cat_id;
  	  $command=Yii::app()->db->createCommand($sql);
  	  $isParent = $command->queryScalar();
	  }catch (Exception $e) //ошибка sql
	  {
	    throw new CHttpException(404,'Запрашиваемая страница не найдена');
	  }
	  
	  if(!$isParent)
	    $this->renderProdByCriteria('cat');
	  else //выводим детей текущей категории
	    $this->renderCatChilds($this->curCat->cat_id);
	}
  
  /**
   * @return Возвращает модель текущей категории в зависимости от параметра, указанного в alias
   */
  public function getCurCat()
  {
    if(empty($_GET['alias'])) return;
    if(!$this->_curCat)
    {
      $criteria = new CDbCriteria;
    
      if(is_numeric($_GET['alias']))
        $criteria->compare('cat_id', $_GET['alias']);
      else
        $criteria->compare('cat_alias', $_GET['alias']);
        
	    $this->_curCat = Categorie::model()->find($criteria);
    }
    
	  return $this->_curCat;
  }
	
	/**
	 * Выводит список брендов или категории, пренадлежащие определенному бренду, а так же описание бренда.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionBrand($alias='')
	{
	  if($alias == '')
	    $this->renderAllBrands();
	  else
    {
      $brand = Brand::model()->with('cats')->findByAttributes(array('brand_alias' => $alias));
      $this->render('brand', array(
        'brand' => $brand,
        'cats' => $brand->cats,
      ));
      //$this->renderProdByCriteria('brand');
    }
	}
	
	/**
	 * Выводит сетку со всеми брендами
	 */
	protected function renderAllBrands()
	{
	  $dataProvider=new CActiveDataProvider('Brand', array(
	    'pagination'=>false,
	  ));
	  $this->render('index',array(
		  'dataProvider'=>$dataProvider,
		  'itemView'=>'_brand',
		  'title' => 'Бренды',
	  ));
	}
	
	/**
	 * Выводит сетку со всеми брендами
	 */
	protected function renderCatChilds($catId)
	{    
	  $dataProvider=new CActiveDataProvider('Categorie', array(
	    'pagination'=>false,
	    'criteria'=>array(
	      'condition'=>'cat_parent='.$catId,
	    ),
	  ));
	  $this->render('index',array(
		  'dataProvider'=>$dataProvider,
		  'itemView'=>'_cat',
		  'title' => $this->curCat->cat_name ? $this->curCat->cat_name : $this->module->params['moduleName'],
	  ));
	}
	
	/**
	 * Выводит все продукты бренда или категории
   * 
   * @param string $name определяет что именно нужно рендерить ('cat' или 'brand')
   * @access protected
   * @return void
   */
	protected function renderProdByCriteria($name)
	{
    $Shop = new Shop();
		// фильтруем как пост так и гет запросы
		$filterData = (Yii::app()->request->isPostRequest) ? $_POST : $_GET;

    $dataProvider = $Shop->filter($filterData);
		
		$dataProvider->criteria->with=array(
      $name => array(
        'condition'=>$name.'.'.$name.'_alias=:alias',
        'params'=>array(':alias'=> $_GET['alias']),
      ),
    );
    
    // POST запросами у нас отправляется фильтр через аякс
    // в этом случае нам нужно вернуть только колличество результатов
    if(Yii::app()->request->isPostRequest)
    {
      unset($filterData['ajax']); // так как в ответе мы будем генерировать ссылку с данными для фильтра, нам надо убрать элемент ajax
      if ($dataProvider->totalItemCount)
        echo "Найдено товаров: <b>" . $dataProvider->totalItemCount . "</b>. " . CHtml::link('Показать', 
          preg_replace('/\?[^\?]*$/','',$_SERVER["REQUEST_URI"]).'?'.http_build_query($filterData));
      else
        echo "Нет подходящих товаров";
      Yii::app()->end();
    }
    
    $data = $dataProvider->getData();
    $title = $data[0]->$name->{$name._name};
    
    switch($name)
    {
      case 'brand':
        $breadcrumbs = array(
			    'Бренды'=>'/shop/brand',
			  );
			break;
			case 'cat':
        $breadcrumbs = $data[0]->cat->parentBreadcrumbs;
        // Виджет фильтра
        $this->beginAside(array(
          'id' => 'shopFilter',
          'title' => 'Фильтр',
        ));
        $this->widget('shop.widgets.filter.Filter');
        $this->endAside();
			break;
    }
    
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'itemView'=>'_view',
			'breadcrumbs'=>$breadcrumbs,
			'title' => $title,
		));
	}
	
	public function actionIndex()
	{
	  //$this->redirect(array('/'));
	  /*$dataProvider=new CActiveDataProvider(Shop::model()->published());
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'itemView'=>'_view',
		));*/
    $_GET['alias'] = 0;
    $this->renderCatChilds(0);      
	}
	
	/**
	 *  Сравнение товаров
	 */
	public function actionCompare($action, $id = false, $_pattern = '<action>(/<id:\d+>)?')
	{
	  if($action == 'add') $this->compareAdd($id);
	  if($action == 'reset') $this->compareRemove($id);
	  if($action == 'remove') $this->compareRemove($id);
    
    // если код добрался сюда, значит нам надо попытаться вывести таблицу сравнения
	  $compare = Yii::app()->session['ProdCompare'];
	  if(!$compare) $this->redirect('/');

	  $this->layout='//layouts/column2';
	  
	  $ids = array_keys($compare[$action]); // теперь в значениях элементов массива находятся id товаров
	  
	  $criteria = new CDbCriteria;
	  $criteria->with = array(
	    'char',
	    'char.charShema',
	  );
	  $criteria->compare('t.id', $ids);
	  
	  $models = Shop::model()->findAll($criteria);
	  foreach($models as $model)
	  {
	    foreach($model->char as $char)
	      $charCompareArr[$char->charShema->char_name][] = $char->char_value;
	  }
	  
	  $this->render('compare', array(
	    'charCompareArr' => is_array($charCompareArr) ? $charCompareArr : array(),
	    'models' => $models,
	  ));
	}
	
	/**
	 *  Добавить товар к сравнению
	 */
	protected function compareAdd($id)
	{
	  if(empty($_GET['cat']) || !$id) return;
	  if(empty(Yii::app()->session['ProdCompare']))
      Yii::app()->session->add('ProdCompare', array());
    
    $compare = Yii::app()->session['ProdCompare'];  
    $compare[$_GET['cat']][$id] = $_GET['thumb'];
    
    Yii::app()->session['ProdCompare'] = $compare;
    Yii::app()->end();
	}
	
	/**
	 *  Удалить товар / все товары из сравнения
	 */
	protected function compareRemove($id)
	{
	  if(isset($_GET['cat']))
	  {
  	  $compare = Yii::app()->session['ProdCompare'];
  	  if(count($compare[$_GET['cat']]) < 2) // в массиве только один элемент, потому его можно полностью удалить из сессии
  	    Yii::app()->session->remove('ProdCompare');
  	  else
  	  {
  	    if($id)
    	    unset($compare[$_GET['cat']][$id]);
    	  else
  	     unset($compare[$_GET['cat']]);
  	    Yii::app()->session['ProdCompare'] = $compare;
  	  }
	  }
	  if(empty($_GET['ajax']))
	    $this->redirect('/');
	  Yii::app()->end();
	}
	
	/**
	 * Changes product rating (AJAX)
   * @param int $id product id
   * @param int $val raing val
	 */
	public function actionRating()
  { 
    $id = $_GET['id'];
    $val = $_GET['val'];
    if ( Yii::app()->request->isAjaxRequest )
    {
      try {
        if(empty($id) || empty($val))
          throw new CDbException('Нету всех необходимых параметров');

        $ratingModel = new Rating;
        $ratingModel->attributes = array(
          'prod_id' => $id,
          'user_id' => Yii::app()->user->id,
          'value' => $val,
        );
        $ratingModel->save();
      }
      catch(CDbException $e) {
        echo CJSON::encode( array (
                              'status'=>'fail', 
                              'answer'=>'Вы уже голосовали за этот продукт!',                                 
                              ) );
        return;
      }

      $model = Shop::model()->published()->findByPk($id); 
      if (empty($model->rating))
        $rating = '1.'.$val*100;
      else
        $rating = ($model->votesCount + 1) . '.' . (($model->ratingVal/100 + $val) / 2 * 100);
      
      $model->rating = (float)$rating;
      $model->save();
      echo CJSON::encode( array (
                              'status'=>'success', 
                              'answer'=>'Спасибо, ваш голос учтен!',                                 
                              ) );
    }
  }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
    $dependency = new CDbCacheDependency('SELECT MAX(edit_date) FROM shop');
	  $model=Shop::model()->cache(3600, $dependency)->published()->with('cat', 'brand')->findByAttributes(array('page_alias'=>$id));
		  
		if($model===null)
			throw new CHttpException(404,'Запрашиваемая страница не найдена');
		return $model;
	}
}
