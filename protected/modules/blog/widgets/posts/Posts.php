<?php	
/**
*  Строит информер с постами
*  Описание параметров ниже
**/
class Posts extends CWidget
{
  // количество колонок с товарами
  public $cols = 2;
  
  // количество строк с товарами
  public $rows = 1;
  
  // сколько товаров показывать (если так и останется false, то значение переопределится в init() )
  public $amount = false;
  
  // вьюха для отображения товара
  public $view = '_post';
    
  protected $assetsUrl;
  
  public function init()
  {
    Yii::import('blog.models.*');
    
    if (!$this->amount) $this->amount = $this->cols * $this->rows;
    
    // регестрируем assets
    //$this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    //$this->registerCssFile('post.css');
    
    // создаем провайдера в зависимости от переданных параметров
    $dataProvider = $this->createProvider();
    
    // рендерим продукты
    $this->renderPosts($dataProvider);
  }
  
  /***********************
  * Создает обьект CActiveDataProvider в зависимости от параметров, переданных виджету
  ***********************/
  protected function createProvider()
  {    
    return new CActiveDataProvider(Post::model()->published()->latest(), array(
      'criteria' => array(
        'limit' => $this->amount,
      ),
      'pagination' => false,
    ));
  }
  
  /***********************
  * Рендерит продукты
  ***********************/
  protected function renderPosts($dataProvider) {
    $posts = $dataProvider->data;
    foreach($posts as $post)
    {
      $this->render($this->view, array(
        'cols' => $this->cols,
        'rows' => $this->rows,
        'data' => $post,
      ));
    }
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
