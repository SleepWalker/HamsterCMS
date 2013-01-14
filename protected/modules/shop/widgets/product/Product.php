<?php	
/**
*  Строит информер с продуктами
*  Описание параметров ниже
**/
class Product extends CWidget
{
  // категория в которой производить поиск продуктов
  public $catId = false;
  
  // количество колонок с товарами
  public $cols = 1;
  
  // количество строк с товарами
  public $rows = 3;
  
  // Контейнер для строчек
  public $rowContainer = false;
  
  // сколько товаров показывать (если так и останется false, то значение переопределится в init() )
  public $amount = false;
  
  // вьюха для отображения товара
  public $view = '_product';
  
  /**
  * определяет какие продукты выводить
  * 'top', // лучшие товары
  * 'latest', // последние товары
  * 'random', // случайные товары
  * 'related', // похожие товары
  * 'associated', // сопутствующие товары
  * 'boughtTogether', // покупаемые вместе 
  * 'sale', // акция
  * 'bestPrice', // "лучшая цена"
  **/
  public $show;
  
  protected $assetsUrl;
  
  public function init()
  {
    Yii::import('shop.models.*');
    
    if (!$this->amount) $this->amount = $this->cols * $this->rows;
    
    // регестрируем assets
    $this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    $this->registerCssFile('product.css');
    
    // создаем провайдера в зависимости от переданных параметров
    $dataProvider = $this->createProvider();
    
    // рендерим продукты
    $this->renderProducts($dataProvider);
  }
  
  /***********************
  * Создает обьект CActiveDataProvider в зависимости от параметров, переданных виджету
  ***********************/
  protected function createProvider()
  {
    switch($this->show) 
    {
      case 'top': // лучшие товары
        $criteria = array(
            'order' => 'rating DESC',
        );
      break;
      default: // последние товары
        $criteria = array(
            'order' => 'edit_date DESC',
        );
      break;
    }
    
    $criteria = array_merge($criteria, array(
      'limit' => $this->amount,
    ));
    
    return new CActiveDataProvider(Shop::model()->published(), array(
        'criteria' => $criteria,
        'pagination' => false,
    ));
  }
  
  /***********************
  * Рендерит продукты
  ***********************/
  protected function renderProducts($dataProvider) {
    $prods = $dataProvider->data;

    // если был задан контейнер для строчек
    if($this->rowContainer)
      echo CHtml::openTag($this->rowContainer);
      
    foreach($prods as $i => $prod)
    {      
      $this->render($this->view, array(
        'cols' => $this->cols,
        'rows' => $this->rows,
        'data' => $prod,
      ));
      
      if($this->rowContainer && ($i+1)%$this->cols == 0)
        echo CHtml::openTag($this->rowContainer);
    }
    
    if($this->rowContainer) 
      echo CHtml::closeTag($this->rowContainer);
  }
  
  protected function registerScriptFile($fileName,$position=CClientScript::POS_END)
  {
    Yii::app()->getClientScript()->registerScriptFile($this->assetsUrl.'/js/'.$fileName,$position);
  }
  
  protected function registerCssFile($fileName)
  {
    Yii::app()->getClientScript()->registerCssFile($this->assetsUrl.'/css/'.$fileName);
  }
}
