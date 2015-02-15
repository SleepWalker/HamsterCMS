<?php
/**
 * Виджет Events
 * Строит информер с постами
 * Описание параметров ниже
 *
 * @uses CWidget
 * @package hamster.widgets.view
 * @version $id$
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su>
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
//TODO: можно сделать, что бы виджет вызывал scope|with на модели, что бы модули могли задать какие именно связи нужно зацепить вместе с основной моделью
class SimpleListView extends CWidget
{
    // количество колонок с товарами
    public $cols = 1;

    // количество строк с товарами
    public $rows = 1;

    // сколько товаров показывать (если так и останется false, то значение переопределится в init() )
    public $amount = false;
    public $offset = -1;

    // вьюха для отображения ивента
    public $view = '_simpleView';

    public $model = false;

    private static $__viewPaths;

    public function init()
    {
        if (!$this->model) {
            throw new CException("No model name or instance provided. See SimpleListView::model");
        }

        if (is_string($this->model) && ($pos = strrpos($this->model, '.')) !== false) {
            $className = substr($this->model, $pos + 1);
            if (!class_exists($className, false))
            // пробуем ипортировать модель
            {
                Yii::import($this->model);
            }

            $this->model = $className;
        }

        if (!$this->amount) {
            $this->amount = $this->cols * $this->rows;
        }

    }

    public function run()
    {
        $dataProvider = $this->createProvider();

        $this->renderItems($dataProvider);
    }

    /**
     * Создает обьект CActiveDataProvider в зависимости от параметров, переданных виджету
     *
     * @access protected
     * @return CActiveDataProvider
     */
    protected function createProvider()
    {
        return new CActiveDataProvider($this->model, array(
            'criteria' => array(
                'limit' => $this->amount,
                'offset' => $this->offset,
            ),
            'pagination' => false,
        ));
    }

    /**
     * Рендерит контент
     *
     * @param CActiveDataProvider $dataProvider
     * @access protected
     * @return void
     */
    protected function renderItems($dataProvider)
    {
        $items = $dataProvider->data;
        foreach ($items as $index => $item) {
            $this->render($this->view, array(
                'cols' => $this->cols,
                'rows' => $this->rows,
                'index' => $index,
                'data' => $item,
            ));
        }
    }

    /**
     * Путь поиска вьюх в теме теме: /viewWidges/[moduleId]
     * Путь поиска вьюх в модуле, которому принадлежит модель: /viewWidgets
     *
     * @param boolean $checkTheme whether to check if the theme contains a view path for the widget.
     * @return string the directory containing the view files for this widget.
     */
    public function getViewPath($checkTheme = false)
    {
        $className = get_class($this);
        $modelName = is_string($this->model) ? $this->model : get_class($this->model);
        $key = $className . $modelName;
        $scope = $checkTheme ? 'theme' : 'local';
        if (isset(self::$__viewPaths[$key][$scope])) {
            return self::$__viewPaths[$key][$scope];
        } else {
            $class = new ReflectionClass($modelName);
            $moduleId = basename(dirname(dirname($class->getFileName())));

            if ($checkTheme && ($theme = Yii::app()->getTheme()) !== null) {
                $path = $theme->getViewPath() . DIRECTORY_SEPARATOR . 'viewWidgets' . DIRECTORY_SEPARATOR . $moduleId;
                if (is_dir($path) && is_file($path . DIRECTORY_SEPARATOR . $this->view . '.php')) {
                    return self::$__viewPaths[$key]['theme'] = $path;
                }

            }

            $class = new ReflectionClass($this->model);
            return self::$__viewPaths[$key]['local'] = dirname(dirname($class->getFileName())) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'viewWidgets';
        }
    }
}
