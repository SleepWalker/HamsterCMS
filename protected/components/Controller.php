<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column2';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

  /**
   * @var array $aside массив с html блоками
   */
  public $aside = array();

  /**
   * @var array $bodyClasses массив с css классами для body
   */
  public $bodyCssClasses = array();

  /**
   * @var array $_asideStack массив-стэк aside блоков. Используется для хранения параметров блока до вызова {@link Controller::endAside()}
   */
  protected $_asideStack = array();
  protected $_asideBottom = array();

  protected function beforeAction($action)
  {
    if(!empty(Yii::app()->params['defaultLayout']))
      $this->layout = '//layouts/' . Yii::app()->params['defaultLayout'];

    Yii::setPathOfAlias('theme', Yii::app()->theme->basePath);

    // Определяем текущий роут, что бы по нему определить лейаут
    if($this->module)
    {
      $route = preg_replace('#' .$this->module->id . '/#', '', $this->route, 1);
      if(isset($this->module->params['routes'][$route]['layout']))
      {
        $this->layout = '//layouts/' . $this->module->params['routes'][$route]['layout'];
      }
    }

    // инициализируем цсс классы
    $layoutId = explode('/', $this->layout);
    $this->bodyCssClasses = array(end($layoutId), $this->id, $this->id . ucfirst($this->action->id));

    return true;
  }

  /**
   * Добавляет блок кода в стэк aside  
   * 
   * @param mixed $content код aside блока или массив array('viewName', 'param1' => $param1, 'param2' => $param2, ...)
   * @param array $params параметры
   *    position => top|bottom|null
   *    id => string
   *    title => string
   *
   * @access public
   * @return void
   */
  public function pushAside($content, $params = array())
  {
    // передана вьюха
    if(is_array($content))
      $content = $this->renderPartial($content[0], array_slice($content, 1), true);

    $position = null;
    if(isset($params['position']))
    {
      $position = $params['position'];
      unset($params['position']);
    }
    $blockSettings = array(
      'portlet' => $params,
      'content' => $content,
    );

    switch($position)
    {
    case 'top':
      array_unshift($this->aside, $blockSettings);
      break;
    case 'bottom':
      array_push($this->_asideBottom, $blockSettings);
      break;
    default:
      array_push($this->aside, $blockSettings);
      break;
    }
  }

  /**
   * Начало блока  
   * 
   * @param array $params 
   * @access public
   * @return void
   */
  public function beginAside(array $params = array())
  {
    array_push($this->_asideStack, $params);
    ob_start();
  }

  /**
   * Конец блока  
   * 
   * @access public
   * @return void
   */
  public function endAside()
  {
    $params = array_shift($this->_asideStack);
    $this->pushAside(ob_get_clean(), $params);
  }

  /**
   * Рендерит блоки  
   * 
   * @param array $options настройки рендеринга блоков
   * @access public
   * @return void
   */
  public function renderAside($options = array())
  {
    $asides = CMap::mergeArray(
      $this->aside,
      // блоки, которые должны быть в самом низу
      $this->_asideBottom
    );

    foreach($asides as $aside)
    {
      if(isset($options['blackList']) && in_array($aside['portlet']['id'], $options['blackList']))
        continue;

      //$this->beginWidget('zii.widgets.CPortlet', $aside['portlet']);
      echo '<div class="block">';
      if(isset($aside['portlet']['title']))
        echo "<h6>{$aside['portlet']['title']}</h6>";
      echo $aside['content'];
      echo '</div>';
      //$this->endWidget();
    }
  }

  public function getBodyCssClass()
  {
    return implode(' ', $this->bodyCssClasses);
  }
	
  /**
   * Отображает ошибку 404  
   * 
   * @access protected
   * @return void
   */
  protected function pageNotFound()
  {
    throw new CHttpException(404, Yii::t('base', 'Запрашиваемая страница не существует'));
  }

  /**
   * Renders a view file.
   * This method is required by {@link IViewRenderer}.
   * @param CBaseController $context the controller or widget who is rendering the view file.
   * @param string $sourceFile the view file path
   * @param mixed $data the data to be passed to the view
   * @param boolean $return whether the rendering result should be returned
   * @return mixed the rendering result, or null if the rendering result is not needed.
   */
  public function renderFile($viewFile,$data=null,$return=false)
  {
    // для mustache шаблонов добавляем дополнительные переменные в массив конфигурации
    if(strpos($viewFile, '.mustache'))
    {
      $controller = $this;
      $aside = new HAsideBlock;
      $mustacheTags = array(
        // {{# pageTitle }}title to set {{/ pageTitle }} или {{ pageTitle }} (выведет сам титл)
        'title' => function ($title = false) use ($controller) {
          if($title)
            $controller->pageTitle = $title;
          else
            return $controller->pageTitle;
        },
        // {{# setLayout}}main{{/ setLayout }} - переключает layout для вьюхи
        'layout' => function($layout) use ($controller) {$controller->layout = '//layouts/'.$layout;},

        'js' => function($code, $mustache) {
          Yii::app()->clientScript->registerScript(__CLASS__.uniqid(), $mustache->render($code), CClientScript::POS_END);
        },

        // добавление и рендеринг блоков
        'aside' => array(
          'extend' => array(
            'block' => $aside->block(),
            'title' => $aside->title(),
          ),
          'render' => function() use ($controller) {ob_start(); $controller->renderAside(); return ob_get_clean();},
        ),
      );
      $data = CMap::mergeArray(
        $data, array ('hamster' => $mustacheTags, 'h' => $mustacheTags)
      );

      if(strpos($viewFile, 'layouts'))
      {
        $data['layouts'] = new HLayouts;
      }
    }

    return parent::renderFile($viewFile, $data, $return);
  }
}

/**
 * Этот класс добавляет возможность использования beginContent и endContent в mustache шаблонах
 */
class HLayouts
{
  public function __isset($name)
  {
    return true;
  }

  public function __get($name)
  {
    return function($val) use ($name) {
      ob_start();
      Yii::app()->controller->beginContent('//layouts/'.$name);
      echo $val;
      Yii::app()->controller->endContent();
      return ob_get_clean();
    };
  }
}

class HAsideBlock
{
  public $title;
  public function title()
  {
    $obj = $this;
    return function($title) use($obj) {
      $obj->title = $title;
    };
  }

  public function block()
  {
    $obj = $this;
    return function($text, $helper) use($obj) {
      $content = $helper->render($text);
      Yii::app()->controller->pushAside($content, array('title' => $obj->title));
    };
  }
}
