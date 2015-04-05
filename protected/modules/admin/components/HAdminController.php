<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */

namespace admin\components;

use application\modules\admin\models\HArrayConfig as HArrayConfig;

class HAdminController extends \CController
{
    /**
     * @property string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '/layouts/column2';
    /**
     * @property array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();
    /**
     * @property array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $aside = array();

    /**
     * @property array $pageActions массив с дополнительными действиями, которые появятся возле тега h1
     */
    public $pageActions;

    public $curModuleUrl; // путь к index текущего модуля, к примеру /admin/shop
    public $adminAssetsUrl;

    public function init()
    {
        if (!isset($this->module) || !($this->module instanceof \admin\AdminModule)) {
            throw new \CException('Дети HAdminController должны запускаться из модуля admin');
        }

        if (preg_match('/\w+AdminController$/', get_class($this))) {
            //импортим модели и компоненты
            $this->module->setImport(array(
                'application.modules.' . $this->id . '.models.*',
                'application.modules.' . $this->id . '.components.*',
            ));
        }
    }

    /**
     * Переопредиляем стандартный метод таким образом, что бы он искал вьюхи в следущем порядке:
     *  - Сначала во вьюхах админки (admin/views/admin/*)
     *  Далее, если активно действие администрирования модуля (AdminAction)
     *    - в темах модуля, который админиться (themes/.../moduleId/admin/*)
     *    - во вьюхах модуля, который админиться (views/moduleId/admin/*)
     *
     *  Примечание: Стандартный метод getViewFile так же будет искать вьюхи модуля admin и в темах,
     *      но так как по умолчанию не рассчитывается, что такие будут, они не вошли в список выше
     *
     * @param string $viewName
     * @access public
     * @return mixed путь к файлу вьюхи или false, если файла не существует
     */
    public function getViewFile($viewName)
    {
        if (!($viewFile = parent::getViewFile($viewName))) {
            $basePath = \Yii::app()->getViewPath();
            if ($this->action instanceof \AdminAction) {
                // попробуем поискать в admin вьюхах текущего модуля
                // @property $this->action->id id текущего модуля, админ часть которого активна.
                $moduleViewPath = \Yii::getPathOfAlias('application.modules.' . $this->action->id) . '/views';
                $viewPath = $moduleViewPath . '/admin';

                $themeBasePath = \Yii::app()->getTheme()->getViewPath();
                $themeModuleViewPath = $themeBasePath . '/' . $this->action->id;
                $themeViewPath = $themeModuleViewPath . '/admin';

                if (!($viewFile = $this->resolveViewFile($viewName, $themeViewPath, $themeBasePath, $themeModuleViewPath))) {
                    $viewFile = $this->resolveViewFile($viewName, $viewPath, $basePath, $moduleViewPath);
                }
            } else {
                // для обычных контроллеров модуля admin, в случае если в их директории нету нужной вьюхи,
                // попробуем поискать ее во вьюхах контроллера AdminController
                $moduleViewPath = $this->module->getViewPath();
                $viewPath = $moduleViewPath . '/admin';
                $viewFile = $this->resolveViewFile($viewName, $viewPath, $basePath, $moduleViewPath);
            }
        }

        return $viewFile;
    }

    /**
     * Возвращает массив конфигурации табов (карта действий)
     *
     * @access public
     * @return array
     */
    public function tabs()
    {
        return array();
    }

    /**
     * Генерирует код для tabs на основе карты действий
     *
     * @return array массив для инициализации меню табов
     */
    public function getTabs()
    {
        if (method_exists($this->action, 'tabs')) {
            $tabMap = $this->action->tabs();
        } else {
            // экшен может переопределить табы, если это нужно
            $tabMap = $this->tabs();
        }

        $tabs = '';

        foreach ($tabMap as $path => $name) {
            if ($path == '') {
                $path = 'index';
            }

            if (is_array($name)) {
                $hide = 0;

                switch ($name['display']) {
                    // Определяем показывать ли этот таб
                    case 'whenActive':
                        if ($this->action->id != $path) {
                            $hide = 1;
                        }

                        break;
                    case 'index':
                        if (!($this->action->id == 'index' || $this->action->id == 'create' || $this->action->id == 'update')) {
                            $hide = 1;
                        }

                        break;
                    default:
                        if (strpos($this->action->id, $name['display']) === false) {
                            $hide = 1;
                        }

                        break;
                }
                if ($hide) {
                    continue;
                }

                $name = $name['name'];
            }

            if ($this->action->id == $path) {
                $this->pageTitle = $name;
            }

            $tabs .= '<a href="' . $this->createUrl($path) . '">' . $name . '</a>';
        }
        return $tabs;
    }

    /**
     * Загружает настройки модулей Hamster
     * @return array массив с настройками
     */
    public function getHamsterModules()
    {
        return $this->module->getHamsterModules();
    }

    /**
     * @return array массив с информацией о модулях
     */
    public function getModulesInfo()
    {
        return $this->module->getModulesInfo();
    }

    /**
     * @return array массив с информацией об активных модулях
     */
    public function getEnabledModules()
    {
        return $this->module->getEnabledModules();
    }

    /**
     * Очищает папки assets и кэш
     *
     * @access protected
     * @return void
     */
    protected function clearTmp()
    {
        // TODO: убрать отсюда. либо в модуль админа, либо в какой-то глобальный класс, аля Hamster
        $this->destroyDir(\Yii::getPathOfAlias('webroot.assets'), false);
        \Yii::app()->cache->flush();
    }

    /**
     * Полностью удаляет содержимое $dir
     * @param string $dir путь к директории
     * @param boolean $removeParent если true, то так же будет удалена директория $dir
     * @see CFileHelper::removeDirectory()
     */
    protected function destroyDir($dir, $removeParent = true)
    {
        $items = glob($dir . DIRECTORY_SEPARATOR . '{,.}*', GLOB_MARK|GLOB_BRACE);
        foreach ($items as $item) {
            if (basename($item) == '.' || basename($item) == '..') {
                continue;
            }

            if (substr($item, -1) == DIRECTORY_SEPARATOR) {
                $this->destroyDir($item);
            } else {
                unlink($item);
            }

        }
        if (is_dir($dir) && $removeParent) {
            rmdir($dir);
        }

    }

    /**
     * Измененный CCOntroler::renderPartial() с целью отключения jQuery при ajax запросах
     *
     * TODO: возможно разместить отключение jQuery в config (там было что-то вроде маппинга скрипта в CClientScript)
     */
    public function renderPartial($view, $data = null, $return = false, $processOutput = false)
    {
        if (isset($_POST['ajaxIframe']) || isset($_POST['ajaxSubmit']) || \Yii::app()->request->isAjaxRequest) {
            \Yii::app()->clientscript->scriptMap['jquery.js'] = \Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
        }

        return parent::renderPartial($view, $data, $return, $processOutput);
    }

    /**
     * Гибридный рендеринг (render/renderPartial) для форм редактирования в админке
     *
     * @access public
     * @return void
     */
    public function renderForm($model, $params = array())
    {
        $params = \CMAp::mergeArray(array('model' => $model), $params);

        if (\Yii::app()->request->isPostRequest) {
            // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
            if (!$model->hasErrors() && $this->action->id == 'create') {
                $data = array(
                    'action' => 'redirect',
                    'content' => $this->curModuleUrl . 'update/' . $model->id,
                );
            } else {
                $data = array(
                    'action' => 'renewForm',
                    'content' => $this->renderPartial('update', $params, true, true),
                );
            }

            header('application/json');
            echo \CJSON::encode($data);
            //echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            \Yii::app()->end();
        } else {
            if (isset($_POST['ajaxIframe']) || isset($_POST['ajaxSubmit']) || \Yii::app()->request->isAjaxRequest) {
                $this->renderPartial('update', $params, false, true);
            } else {
                $this->render('update', $params);
            }

        }
    }

    /**
     * Возвращает id редактируемого материала
     */
    public function getCrudid()
    {
        if (empty($_GET['id'])) {
            return null;
        }

        return $_GET['id'];
    }

    /**
     * Возвращает тип выполняемого crud действия
     */
    public function getCrud()
    {
        if (YII_DEBUG) {
            throw new \CException(__CLASS__.'::getCrud - deparecated. use $this->action->id instead');
        }

        $action = $_GET['action'];
        $parts = explode('/', $action);
        if (strpos($action, 'create') !== false) {
            $crud = 'create';
        }

        if (strpos($action, 'update') !== false) {
            $crud = 'update';
        }

        if (strpos($action, 'delete') !== false) {
            $crud = 'delete';
        } else {
            $crud = array_pop($parts);
        }

        return $crud;
    }
}