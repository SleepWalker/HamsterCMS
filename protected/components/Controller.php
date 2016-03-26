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
    public $layout = '//layouts/column2';
    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();

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
        if (!empty(Yii::app()->params['defaultLayout'])) {
            $this->layout = '//layouts/' . Yii::app()->params['defaultLayout'];
        }

        Yii::setPathOfAlias('theme', Yii::app()->theme->basePath);

        // Определяем текущий роут, что бы по нему определить лейаут
        if ($this->module) {
            $route = preg_replace('#' . $this->module->id . '/#', '', $this->route, 1);
            if (isset($this->module->params['routes'][$route]['layout'])) {
                $this->layout = '//layouts/' . $this->module->params['routes'][$route]['layout'];
            }
        }

        // инициализируем цсс классы
        $layoutId = explode('/', $this->layout);
        $this->bodyCssClasses = array('layout-' . end($layoutId), 'page-' . $this->id, 'page-' . $this->id . ucfirst($this->action->id));

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
        if (is_array($content)) {
            $content = $this->renderPartial($content[0], array_slice($content, 1), true);
        }

        $position = null;
        if (isset($params['position'])) {
            $position = $params['position'];
            unset($params['position']);
        }
        $blockSettings = array(
            'portlet' => $params,
            'content' => $content,
        );

        switch ($position) {
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

        foreach ($asides as $aside) {
            if (isset($options['blackList']) && in_array($aside['portlet']['id'], $options['blackList'])) {
                continue;
            }

            //$this->beginWidget('zii.widgets.CPortlet', $aside['portlet']);
            echo '<div class="block">';
            if (isset($aside['portlet']['title'])) {
                echo "<h6>{$aside['portlet']['title']}</h6>";
            }

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
}
