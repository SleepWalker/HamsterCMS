<?php
/**
 * Admin module main file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.AdminModule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin;

use application\modules\admin\models\HArrayConfig as HArrayConfig;

class AdminModule extends \CWebModule
{
    public $name;
    public $assetsUrl;

    public $controllerNamespace = '\admin\controllers';

    public function init()
    {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application

        // import the module-level models and components
        $this->setImport(array(
            'admin.models.*',
            'admin.components.*',
        ));

        $this->setComponents([
            'moduleManager' => [
                'class' => '\admin\components\HModuleManager',
            ],
        ]);

        $this->assetsUrl = \Yii::app()->getAssetManager()->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets', false, -1, YII_DEBUG); //Yii::getPathOfAlias('application.modules.admin.assets'));
        //$this->registerScriptFile('admin.js');
        //$this->registerCssFile('admin.css');

        // меняем имя сайта
        \Yii::app()->name = 'HamsterCMS';

        // переопределяем страницу входа
        \Yii::app()->user->loginUrl = \Yii::app()->createUrl('admin/login/index');

        // устанавливаем экшен для отобраения ошибок
        \Yii::app()->errorHandler->errorAction = 'admin/admin/error';

        $this->controllerMap = $this->getControllerMap();
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action)) {
            // this overwrites everything in the controller
            $controller->adminAssetsUrl = $this->assetsUrl;

            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        } else {
            return false;
        }

    }

    /**
     * Возвращает список идентификаторов лейаутов доступных для CMS
     * @return array идентификаторы
     */
    public static function getLayoutIds()
    {
        $extension = '.php';
        $layoutPathes = array(\Yii::app()->getViewPath());
        if (($theme = \Yii::app()->getTheme()) !== null) {
            // у нас используются темы
            $layoutPathes[] = $theme->getViewPath();
        }

        $ids = array();
        foreach ($layoutPathes as $curPath) {
            $curPath .= DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
            foreach (glob($curPath . '*' . $extension) as $curFile) {
                $id = basename($curFile, $extension);
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    public function getControllerMap()
    {
        $enabledModules = $this->enabledModules;
        return $enabledModules;
    }

    /**
     * Загружает настройки модулей Hamster
     * @return array массив с настройками
     */
    public function getHamsterModules()
    {
        return $this->moduleManager->getHamsterModules();
    }

    /**
     * @return array массив с информацией о модулях
     */
    public function getModulesInfo()
    {
        return $this->moduleManager->getModulesInfo();
    }

    /**
     * @return array массив с информацией об активных модулях
     */
    public function getEnabledModules()
    {
        return $this->moduleManager->getEnabledModules();
    }

    public function registerScriptFile($fileName, $position = \CClientScript::POS_END)
    {
        \Yii::app()->getClientScript()->registerScriptFile($this->assetsUrl . '/js/' . $fileName, $position);
    }

    public function registerCssFile($fileName)
    {
        \Yii::app()->getClientScript()->registerCssFile($this->assetsUrl . '/css/' . $fileName);
    }
}
