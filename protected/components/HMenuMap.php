<?php
/**
 * HMenuMap класс предназначенный для сбора информации о структуре навигации сайта.
 * 
 * @uses CComponent
 * @package hamster.components.HMenuMap
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */

/**
 * Класс дает возможность отобразить меню, в котором находятся все подпункты текущего меню в навигации. Так же он присваивает класс active активному пункту меню и его родителям.
 *
 * Принцип действия: класс пропускает через себя все меню в шаблоне и сохраняет их в массив, который в последствии кэшируется (необходимо, что бы получить массив на более низких уровнях, до того, как меню попали внутрь класса). 
 *
 * Использование:
 * все меню в шаблоне рендерим с помощью Yii::app()->menuMap->render(array $menu, [mixed $menuId]). Массив $menu имеет такую же структуру как и массив для стандартного виджета Yii CMenu. Внимание, что бы определение активных пунктов меню работало нужно использовать вариант инициализации url с помощью роутов. Пример:
 *  Yii::app()->menuMap->render(array(
 *    'Публикации' => array('blog/blog/index'),
 *    'Мероприятия' => array('event/event/index'),
 *    'Пресса о нас' => array('page/index', 'path' => 'presse'),
 *  ));
 *
 *  Далее, в том месте, где нам нужно отобразить все пункты меню, находящиеся на одном уровне с текущей страницей пишим следующее:
 *    <?php if(Yii::app()->menuMap->hasSuggestions) { ?>
 *      <nav class="block">
 *      <?php
 *        Yii::app()->menuMap->suggest();
 *      ?>    
 *      </nav>
 *    <?php } ?>
 */


class HMenuMap extends CApplicationComponent
{
  // карта меню и подменю
  protected $_menuMap = array();
  // карта маршрутов для ускорения поиска по меню
  protected $_routeMap = array();
  protected $_cachedRouteMap;

  protected $_reCache = false;
  protected $CACHE_ID;

  // public init() {{{ 
  /**
   * Подгружаем из кэша массив с информацией о меню
   * 
   * @access public
   * @return void
   */
  public function init()
  {
    parent::init();
    $module = Yii::app()->controller->module;
    $this->CACHE_ID = ($module && $module->id == 'admin')
      ?'hamster.components.HMenuMap.admin'
      :'hamster.components.HMenuMap';
    $this->_cachedRouteMap = Yii::app()->cache->get($this->CACHE_ID);
    $this->_reCache = $this->_cachedRouteMap == null;
  }
  // }}}

  // public add(arraymenu,menuId=false) {{{ 
  /**
   * Добавляет меню в массив-карту всех меню
   * 
   * @param array $menu 
   * @param mixed $menuId 
   * @access public
   * @return void
   */
  public function add(array $menu, $menuId = false)
  {
    if($menuId === false) $menuId = (int)count($this->_menuMap);
    $this->_menuMap[$menuId] = $menu;
    $parentId = 0;
    return array($menuId, $parentId);
  }
  // }}}

  // public render(arraymenu,menuId=false) {{{ 
  /**
   * Рендерит меню
   * 
   * @param array $menu 
   * @param mixed $menuId это может быть либо строка с ид для текущего меню, либо массив в котором могут содержаться элементы id и parent (если нужно привязать это меню к родителю). Оба параметра не являются обязательными
   * @access public
   * @return void
   */
  public function render(array $menu, $menuId = false)
  {
    $menuId = $this->add($menu, $menuId);
    $this->renderInner($menu);
  }
  // }}}

  // protected renderInner(menu,cached=false) {{{ 
  /**
   * Внутренний метод для рендеринга меню
   *
   * @param mixed $menu 
   * @param mixed $cached 
   * @access protected
   * @return void
   */
  protected function renderInner($menu, $cached = false)
  {
    foreach($menu as $label => $route)
    {
      if(is_array($route))
      {
        if(!$cached) $this->mapRoute($route, $menu);
        // FIXME: тут тоже особое условие для контроллера page
        // Определяем активную страницу
        if($this->route == $route[0] && ($this->route != 'page/index' || $_GET['path'] == $route['path']))
          $htmlOptions = array('class' => 'active');
        else
          $htmlOptions = array();
        echo CHtml::link($label, Yii::app()->createUrl(array_shift($route), $route), $htmlOptions);
      }else
        echo CHtml::link($label, $route);
    }
  }
  // }}}

  // public mapRoute(route,menu) {{{ 
  /**
   * Добавляет меню в карту путей
   * 
   * @param mixed $route 
   * @param mixed $menu 
   * @access public
   * @return void
   */
  public function mapRoute($route, $menu)
  {
    $this->_routeMap[array_shift($route)][] = array(
      'params' => $route,
      'menu' => &$menu,
    );
  }
  // }}}

  // public suggest() {{{ 
  /**
   * Рендерит меню с пунктами относящимися к текущей странице
   * 
   * @access public
   * @return void
   */
  public function suggest()
  {
    $suggestedRoutes = $this->_cachedRouteMap[$this->route];
    if(is_array($suggestedRoutes))
      foreach($suggestedRoutes as $item)
      {
        //TODO: лучьше переделать контроллер page таким образом, что бы пути выглядили так: page/{pageId}
        //FIXME: Судя по всему, единственный случай, когда нам надо проверять страницы - контроллер страниц, потому добавим для него отдельное условие
        if($this->route == 'page/index' && $item['params'] != $_GET) continue;
        $this->renderInner($item['menu'], true);
      }
  }
  // }}}

  // public getHasSuggestions() {{{ 
  /**
   * Возвращает true если у текущей страницы есть братья и сестры 
   * 
   * @access public
   * @return void
   */
  public function getHasSuggestions()
  {
    return isset($this->_cachedRouteMap[$this->route]) && is_array($this->_cachedRouteMap[$this->route]);
  }
  // }}}
  
  // public getRoute() {{{ 
  /**
   * Возвращает путь текущей страницы
   * 
   * @access public
   * @return string
   */
  public function getRoute()
  {
    $controller = Yii::app()->controller;
    $route = array($controller->id, $controller->action->id);
    if($controller->module)
      array_unshift($route, $controller->module->id);
    return implode('/', $route);
  }
  // }}}

  // public getMenu(menu) {{{ 
  /**
   * Возвращает масив с настройками меню 
   * 
   * @param array $menu 
   * @access public
   * @return array
   */
  public function getMenu($menuId)
  {
    //TODO: На данном этапе этот метод работает только в случае, если он вызывается после того, как карта меню была создана
    //в будущем нужно бы сделать эту функцию рабочей в любом месте кода
    return isset($this->_cachedRouteMap['__menu'][$menuId]) ? $this->_cachedRouteMap['__menu'][$menuId] : '';
  }
  // }}}

  // public __destruct() {{{ 
  /**
   * Деструктор производит запись параметров меню в кэш, если это необходимо.
   * 
   * @access public
   * @return void
   */
  public function __destruct()
  {
    if($this->_reCache || $this->_menuMap != $this->_cachedRouteMap['__menu'])
    {
      $this->_routeMap['__menu'] = $this->_menuMap;
      Yii::app()->cache->set($this->CACHE_ID, $this->_routeMap); //Кэшируем на всегда, так как в случае изменения меню кэш и так будет обновлятся
    }
  }
  // }}}
}
?>
