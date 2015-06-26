<?php
/**
 * ConfigController class for admin module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class ConfigController extends \admin\components\HAdminController
{
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'roles' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     *  Действие, для управления настройками модулей
     */
    public function actionIndex()
    {
        $this->pageTitle = 'Настройки Hamster';

        $config = \admin\models\HArrayConfig::load();

        $modulesMenu['/admin/config'] = 'Основные настройки';
        foreach ($this->modulesInfo as $moduleId => $moduleInfo) {
            $isEnabled = isset($this->enabledModules[$moduleId]);
            $onoffLabel = array('switchOff', 'switchOn');
            $modulesMenu['/admin/config?m=' . $moduleId] = '<b onclick="location.href=\'/admin/config/switchmodule?m=' . $moduleId . '\'; return false;" class="' . $onoffLabel[$isEnabled] . '"></b> ' . $moduleInfo['title'];
        }
        $this->aside['Доступные модули<a href="/admin/config/modulediscover" class="icon_refresh"></a><a href="/admin/config/clearTmp" class="icon_delete"></a>'] = $modulesMenu;

        if (isset($_GET['m'])) {
            $moduleId = $_GET['m'];

            $schema = \Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/configSchema.php';
            if (file_exists($schema)) {
                $schema = require $schema;
            }

            // TODO: module ROOT секция
            $cformConfig = array(
                'buttons' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'label' => 'Сохранить',
                        'attributes' => array(
                            'class' => 'submit',
                            'id' => 'submit',
                        ),
                    ),
                ),
                'elements' => array(
                ),
            );

            $localSchema = $schema;
            unset($localSchema['hamster']);

            // добавляем поля url и имя модуля, которые будут отображаться на сайте
            if (!isset($localSchema['moduleName'])) {
                $localSchema['moduleName'] = array(
                    'label' => 'Название модуля',
                    'type' => 'text',
                    'default' => ucfirst($moduleId),
                );
            }

            if (!isset($localSchema['moduleUrl'])) {
                $localSchema['moduleUrl'] = array(
                    'label' => 'URI Адрес модуля',
                    'type' => 'text',
                    'default' => $moduleId,
                );
            }

            if (!isset($schema['hamster']['admin']['routes'])) {
                throw new \CException("Модуль должен содержать информацию об используемых в нем путях ['hamster']['admin']['routes']");
            }

            $routes = $schema['hamster']['admin']['routes'];
            $routesCformConfig = array(
                'layout' => array(
                    'label' => 'Layout модуля',
                    'type' => 'dropdownlist',
                    'items' => \admin\AdminModule::getLayoutIds(),
                ),
                'alias' => array(
                    'label' => 'Псевдоним пути',
                    'type' => 'text',
                ),
            );
            // TODO: tabeTitle
            foreach ($routes as $route => $params) {
                if (is_numeric($route)) {
                    // модуль задал только роуты, без дефолтных значений
                    $route = $params;
                    $params = array();
                }

                $curConfig = $routesCformConfig;
                $curConfig['alias']['default'] = isset($params['default']) ? $params['default'] : $route;

                $localSchema['routes[' . $curConfig['alias']['default'] . '][layout]'] = $curConfig['layout'];
                $localSchema['routes[' . $curConfig['alias']['default'] . '][alias]'] = $curConfig['alias'];
            }

            $local = $config->{'get' . $moduleId . 'ParamsModel'}($localSchema);

            $cformConfig['elements']['local'] = array(
                'type' => 'form',
                'model' => $local,
                'elements' => $local->cformConfig,
            );

            if (isset($schema['hamster']['global']) && count($schema['hamster']['global'])) {
                $globalSchema = $schema['hamster']['global'];
                $global = $config->{'getParamsModel'}($globalSchema);

                $cformConfig['elements']['global'] = array(
                    'type' => 'form',
                    'model' => $global,
                    'elements' => $global->cformConfig,
                );
            }

            $info = $config->{'get' . $moduleId . 'InfoModel'}(array(
                'title' => array(
                    'label' => 'Название модуля в админ панели',
                    'default' => isset($schema['hamster']['admin']['title']) ? $schema['hamster']['admin']['title'] : $moduleId,
                    'type' => 'text',
                ),
            ));

            $cformConfig['elements']['admin'] = array(
                'type' => 'form',
                'model' => $info,
                'elements' => $info->cformConfig,
            );

            $cform = new \CForm($cformConfig);

        } else {
            // $config = new \admin\models\Config::(array(), 'admin');

            // TODO: написать еще одну прослойку, которая будет принимать полный конфиг и хранить при себе все сгенерированные модели и потом уже выдавать CForm
            /*
            'global' => array(
            'type' => 'form',
            'model' => $global,
            'elements' => $global->cformConfig,
            ),
            ЗАМЕНИТЬ НА:
            'global' => array(
            'type' => 'form',
            'model' => $global, // <-- автоматически
            'elements' => array(...),
            ),
             */
            $root = $config->getRootModel(array(
                'name' => array(
                    'type' => 'text',
                    'label' => 'Название сайта',
                    'default' => 'Another Hamster Site',
                ),
            ));

            $params = $config->getParamsModel(array(
                'defaultLayout' => array(
                    'label' => 'Шаблон по умолчанию',
                    'type' => 'dropdownlist',
                    'items' => \admin\AdminModule::getLayoutIds(),
                ),
                'shortName' => array(
                    'label' => 'Короткое имя сайта, которым будут подписываться, к примеру, письма от сайта',
                    'type' => 'text',
                ),
                'adminEmail' => array(
                    'label' => 'Емейл администратора',
                    'type' => 'email',
                ),
                'noReplyEmail' => array(
                    'label' => 'Емейл робота (Например: noreply@mysite.com)',
                    'type' => 'email',
                ),
                'i18n[enabled]' => array(
                    'label' => 'Активировано',
                    'type' => 'checkbox',
                ),
                'i18n[languages]' => array(
                    'label' => 'Языки',
                    'type' => 'checkboxlist',
                    'items' => \Hi18nBehavior::getLanguages(),
                ),
            ));

            $db = $config->getDbComponentModel(array(
                'connectionString' => array(
                    'label' => 'Строка соединения с БД',
                    'type' => 'text',
                    'hint' => 'mysql:host=<b>ХОСТ_БД</b>;dbname=<b>ИМЯ_БД</b>',
                ),
                'username' => array(
                    'label' => 'Имя пользователя',
                    'type' => 'text',
                ),
                'password' => array(
                    'label' => 'Пароль',
                    'type' => 'password',
                ),
            ));

            $cform = new \CForm(array(
                'buttons' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'label' => 'Сохранить',
                        'attributes' => array(
                            'class' => 'submit',
                            'id' => 'submit',
                        ),
                    ),
                ),
                'elements' => array(
                    'root' => array(
                        'type' => 'form',
                        'model' => $root,
                        'elements' => $root->cformConfig,
                    ),

                    'params' => array(
                        'type' => 'form',
                        'title' => 'Настройки глобальных параметров Hamster',
                        'model' => $params,
                        'elements' => array(
                            'defaultLayout' => $params->cformConfig['defaultLayout'],
                            'shortName' => $params->cformConfig['shortName'],
                            'adminEmail' => $params->cformConfig['adminEmail'],
                            'noReplyEmail' => $params->cformConfig['noReplyEmail'],
                            'i18n' => array(
                                'type' => 'form',
                                'title' => 'Интернационализация',
                                'elements' => array(
                                    'i18n[enabled]' => $params->cformConfig['i18n[enabled]'],
                                    'i18n[languages]' => $params->cformConfig['i18n[languages]'],
                                ),
                            ),
                        ),
                    ),
                    'components' => array(
                        'title' => 'Настройки компонентов Hamster',
                        'type' => 'form',
                        'elements' => array(
                            'db' => array(
                                'title' => 'Настройки базы данных',
                                'type' => 'form',
                                'model' => $db,
                                'elements' => $db->cformConfig,
                            ),
                        ),
                    ),
                ),
            ));
            /*
        $config->addConfigFields(array(
        'name' => array(
        'type' => 'text',
        'label' => 'Название сайта',
        'default' => 'Another Hamster Site',
        'linkTo' => '$config["name"]',
        ),
        'params' => array(
        'title' => 'Настройки глобальных параметров Hamster',
        'type' => 'fieldset',
        'elements' => array(
        'defaultLayout' => array(
        'label' => 'Шаблон по умолчанию',
        'type' => 'text',
        'default' => 'column2',
        ),
        'shortName' => array(
        'label' => 'Короткое имя сайта, которым будут подписываться, к примеру, письма от сайта',
        'type' => 'text',
        ),
        'adminEmail'=> array(
        'label' => 'Емейл администратора',
        'type' => 'email',
        ),
        'noReplyEmail'=> array(
        'label' => 'Емейл робота (Например: noreply@mysite.com)',
        'type' => 'email',
        ),
        'i18n'=>array(
        'title' => 'Интернационализация',
        'type' => 'fieldset',
        'elements' => array(
        'enabled' => array(
        'label' => 'Активировано',
        'type' => 'checkbox',
        ),
        'languages' => array(
        'label' => 'Языки',
        'type' => 'checkboxlist',
        'items' => \Hi18nBehavior::getLanguages(),
        ),
        ),
        ),
        ),
        'linkTo' => '$config["params"]',
        ),
        'components' => array(
        'title' => 'Настройки компонентов Hamster',
        'type' => 'fieldset',
        'elements' => array(
        'db' => array(
        'title' => 'Настройки базы данных',
        'type' => 'fieldset',
        'elements' => array(
        'connectionString' => array(
        'label' => 'Строка соединения с БД',
        'type' => 'text',
        'hint' => 'mysql:host=<b>ХОСТ_БД</b>;dbname=<b>ИМЯ_БД</b>',
        ),
        'username' => array(
        'label' => 'Имя пользователя',
        'type' => 'text',
        ),
        'password' => array(
        'label' => 'Пароль',
        'type' => 'password',
        ),
        ),
        ),
        ),
        'linkTo' => '$config["components"]',
        ),
        ));
         */
        }

        if (\Yii::app()->request->isPostRequest) {
            if ($cform->submitted('submit') && $cform->validate()) {
                $config->mergeConfigs();
                // сохраняем роуты в параметры модуля в более удобном виде alias => route
                $localConfig = $config->{'get' . $moduleId . 'Params'}();
                if (isset($_GET['m'])) {
                    foreach ($local->routes as $key => $params) {
                        $localConfig['aliases'][$params['alias']] = array(
                            'route' => $key,
                            'layout' => $params['layout'],
                        );
                    }
                }
                $config->{'set' . $moduleId . 'Params'}($localConfig);

                if (!$config->save()) {
                    \Yii::app()->user->setFlash('error', 'При сохранении конфигурации возникли ошибки');
                } else {
                    \Yii::app()->user->setFlash('success', 'Конфигурация модуля успешно обновлена.');
                }

                $this->refresh();
            }
        }

        if (isset($_GET['m'])) {
            $this->pageTitle = $newPageTitle = $info->title;
        }

        if ($cform) {
            echo $this->render('cform_update', array(
                'form' => $cform,
            ));
        } else {
            $this->renderText('У этого модуля нету настроек для редактирования');
        }
    }

    /**
     * actionClearTmp очищает кэш всех приложений и папку assets
     *
     * @access public
     * @return void
     */
    public function actionClearTmp()
    {
        $this->clearTmp();
        \Yii::app()->user->setFlash('success', 'Кэш и assets были успешно очищены.');
        $this->redirect('/admin/config');
    }

    /**
     * Действие, определяющее наличие модулей в системе
     */
    public function actionModuleDiscover()
    {
        $this->module->moduleManager->discoverModules();

        \Yii::app()->user->setFlash('success', 'Список доступных модулей успешно обновлен. Добавлено модулей: ' . count($this->modulesInfo));
        $this->redirect('/admin/config');
    }

    /**
     * Включает или выключает модуль, переданный в $_GET['m'], после чего редиректит на /admin/config?m=...
     */
    public function actionSwitchModule()
    {
        $moduleName = \Yii::app()->request->getParam('m');
        if ($moduleName) {
            $moduleManager = $this->module->moduleManager;

            $moduleManager->switchModule($moduleName);

            $redirectParams = '';
            if ($moduleManager->isModuleEnabled($moduleName)) {
                $redirectParams = '?m=' . $moduleName;
            }

            $this->redirect('/admin/config' . $redirectParams);
        }
    }
}
