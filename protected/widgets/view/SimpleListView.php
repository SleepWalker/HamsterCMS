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
    public $criteria = array();

    // вьюха для отображения ивента
    public $view = '_simpleView';

    public $model = false;

    private static $_viewPaths;
    protected $_models;

    public function init()
    {
        if (!$this->model && !isset($this->_models)) {
            throw new CException("No models or model name or instance provided. See SimpleListView::model");
        }

        if (is_string($this->model) && ($pos = strrpos($this->model, '.')) !== false) {
            $className = substr($this->model, $pos + 1);
            if (!class_exists($className, false)) {
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
        $models = $this->getModels();

        $this->renderItems($models);
    }

    /**
     * Создает обьект CActiveDataProvider в зависимости от параметров, переданных виджету
     *
     * @access protected
     * @return array
     */
    protected function getModels()
    {
        if (isset($this->_models)) {
            return $this->_models;
        }

        $model = $this->model;

        if (is_string($model)) {
            $model = call_user_func($model . '::model');
        }

        $criteria = new CDbCriteria();
        $criteria->limit = $this->amount;
        $criteria->offset = $this->offset;

        $criteria->mergeWith($this->criteria);

        return $model->findAll($criteria);
    }

    protected function setModels($models) {
        $this->model = get_class($models[0]);
        $this->_models = $models;
    }

    /**
     * Рендерит контент
     *
     * @param CActiveDataProvider $dataProvider
     * @access protected
     * @return void
     */
    protected function renderItems($models)
    {
        foreach ($models as $index => $model) {
            $this->render($this->view, array(
                'cols' => $this->cols,
                'rows' => $this->rows,
                'index' => $index,
                'data' => $model,
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
        if (isset(self::$_viewPaths[$key][$scope])) {
            return self::$_viewPaths[$key][$scope];
        } else {
            $class = new ReflectionClass($modelName);
            $moduleId = basename(dirname(dirname($class->getFileName())));

            if ($checkTheme && ($theme = Yii::app()->getTheme()) !== null) {
                $path = $theme->getViewPath() . '/viewWidgets/' . $moduleId;
                if (is_dir($path) && is_file($path . '/' . $this->view . '.php')) {
                    return self::$_viewPaths[$key]['theme'] = $path;
                }

            }

            $class = new ReflectionClass($this->model);
            return self::$_viewPaths[$key]['local'] = dirname(dirname($class->getFileName())) . '/views/viewWidgets';
        }
    }
}
