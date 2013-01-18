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
   * @property array $aside массив с html блоками
   */
  public $aside = array();
  /**
   * @property array $_asideStack массив-стэк aside блоков. Используется для хранения параметров блока до вызова {@link Controller::endAside()}
   */
  protected $_asideStack = array();
  protected $_asideBottom = array();

  /**
   * Добавляет блок кода в стэк aside  
   * 
   * @param string $content код aside блока
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
    $position = $params['position'];
    unset($params['position']);
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

    //FIXME: настроить портлеты
    foreach($asides as $aside)
    {
      if(is_array($options['blackList']) && in_array($aside['portlet']['id'], $options['blackList']))
        continue;

      $this->beginWidget('zii.widgets.CPortlet', $aside['portlet']);
      echo $aside['content'];
      $this->endWidget();
    }
  }
}
