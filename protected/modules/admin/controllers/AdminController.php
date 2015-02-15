<?php
/**
 * AdminController class for admin module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

use application\modules\admin\models\HArrayConfig as HArrayConfig;

class AdminController extends HAdminController
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
            array('allow',
                'actions' => array('shop', 'error', 'index', 'cart', 'blog', 'page'),
                'roles' => array('staff'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
    //expression: specifies a PHP expression whose value indicates whether this rule matches. In the expression, you can use variable $user which refers to Yii::app()->user.

    public function actionIndex()
    {
        $this->layout = 'main';
        $this->render('index');
    }

    public function actionLogs()
    {
        // Create filter model and set properties
        // http://www.yiiframework.com/wiki/232/using-filters-with-cgridview-and-carraydataprovider/
        $filtersForm = new FiltersForm;
        if (isset($_GET['FiltersForm'])) {
            unset($_GET['FiltersForm'][0]);
            $filtersForm->filters = $_GET['FiltersForm'];
        }

        $logString = file_get_contents(Yii::getPathOfAlias('application.runtime.application') . '.log');
        // добавляем разделитель, по которому будем делить строку
        $logString = preg_replace('/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/m', '--Separator--$0', $logString);
        // Добавляем еще один сепаратор, что бы отображалась и последняя запись в логе
        $logString .= '--Separator--';
        preg_match_all('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[([^\]]+)\] \[([^\]]+)\] (.*?)--Separator--/s', $logString, $matches, PREG_SET_ORDER);
        $matches = array_reverse($matches);
        $filteredData = $filtersForm->filter($matches);
        foreach ($matches as $row) {
            $categories[$row[3]] = $row[3];
        }

        asort($categories);

        $dataProvider = new CArrayDataProvider($filteredData, array(
            'id' => 'log',
            'keyField' => false,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));

        $this->render('log', array(
            'dataProvider' => $dataProvider,
            'filtersForm' => $filtersForm,
            'categories' => $categories,
        ));
    }

    public function actionBackup()
    {
        Yii::import('admin.extensions.yii-database-dumper.SDatabaseDumper');

        if (!is_dir(Yii::getPathOfAlias('application.runtime.backup'))) {
            mkdir(Yii::getPathOfAlias('application.runtime.backup'));
        }
        // создаем директорию для дампов

        $filePath = Yii::getPathOfAlias('application.runtime.backup') . DIRECTORY_SEPARATOR;

        // Восстановление из бекапа
        if (isset($_GET['restore']) && $_GET['restore']) {
            $sqlFile = $filePath . $_GET['restore'];
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if (strpos($sqlFile, 'gz')) {
                    $sql = gzinflate(substr($sql, 10, -8));
                }
                // чистим бд
                $dumper = new SDatabaseDumper;
                $dumper->flushDb();

                // запускаем sql комманды
                $db = Yii::app()->db;
                $command = $db->createCommand($sql);
                $rowCount = $command->execute();

                Yii::app()->user->setFlash('success', 'База успешно восстановлена. Затронуто строк: ' . $rowCount);
            }
            // T!: сделать отправку на восстановление из бекапа через пост
            $this->redirect(array('/admin/backup'));
        }

        // удаление бекапа
        if (isset($_GET['delete']) && $_GET['delete']) {
            if (file_exists($filePath . $_GET['delete'])) {
                if (unlink($filePath . $_GET['delete']) === true) {
                    Yii::app()->user->setFlash('success', 'Бекап ' . $_GET['delete'] . ' успешно удален');
                }

            }
            $this->redirect(array('/admin/backup'));
        }

        if (Yii::app()->request->isPostRequest) {
            if (isset($_POST['flushDb']) && $_POST['flushDb']) {
                $dumper = new SDatabaseDumper;
                if ($dumper->flushDb()) {
                    Yii::app()->user->setFlash('success', 'База успешно очищена');
                }

            } else {
                $dumper = new SDatabaseDumper;
                // Get path to backup file
                $file = $filePath . 'dump_' . date('Y-m-d_H_i_s') . '.sql';

                $dump = $dumper->getDump();
                // Gzip dump
                if (function_exists('gzencode')) {
                    file_put_contents($file . '.gz', gzencode($dump));
                } else {
                    file_put_contents($file, $dump);
                }

            }
            $this->refresh();
        }

        // список файлов в директории
        $fileListOfDirectory = array();
        $pathTofileListDirectory = Yii::getPathOfAlias('application.runtime.backup');
        foreach (new DirectoryIterator($pathTofileListDirectory) as $file) {
            if ($file->isFile() === true) {
                array_push($fileListOfDirectory, array(
                    'name' => $file->getBasename(),
                    'size' => $file->getSize(),
                    'time' => $file->getMTime(),
                ));
            }
        }

        $dataProvider = new CArrayDataProvider($fileListOfDirectory, array(
            'keyField' => false,
            'pagination' => array(
                'pageSize' => 20,
            ),
            'sort' => array(
                'attributes' => array(
                    'time',
                ),
                'defaultOrder' => array(
                    'time' => CSort::SORT_DESC,
                ),
            ),
        ));

        $this->render('backup', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     *  Действие, для управления настройками модулей
     */
    public function actionConfig()
    {
        $this->pageTitle = 'Настройки Hamster';

        $config = HArrayConfig::load();

        $modulesMenu['/admin/config'] = 'Основные настройки';
        foreach ($this->modulesInfo as $moduleId => $moduleInfo) {
            $isEnabled = isset($this->enabledModules[$moduleId]);
            $onoffLabel = array('switchOff', 'switchOn');
            $modulesMenu['/admin/config?m=' . $moduleId] = '<b onclick="location.href=\'/admin/switchmodule?m=' . $moduleId . '\'; return false;" class="' . $onoffLabel[$isEnabled] . '"></b> ' . $moduleInfo['title'];
        }
        $this->aside['Доступные модули<a href="/admin/modulediscover" class="icon_refresh"></a><a href="/admin/clearTmp" class="icon_delete"></a>'] = $modulesMenu;

        if (isset($_GET['m'])) {
            $moduleId = $_GET['m'];

            $schema = Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/configSchema.php';
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
                throw new CException("Модуль должен содержать информацию об используемых в нем путях ['hamster']['admin']['routes']");
            }

            $routes = $schema['hamster']['admin']['routes'];
            $routesCformConfig = array(
                'layout' => array(
                    'label' => 'Layout модуля',
                    'type' => 'dropdownlist',
                    'items' => AdminModule::getLayoutIds(),
                ),
                'alias' => array(
                    'label' => 'Псевдоним пути',
                    'type' => 'text',
                ),
            );
            // TODO: tabeTitle
            foreach ($routes as $route => $params) {
                if (is_numeric($route)) // модуль задал только роуты, без дефолтных значений
                {
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

            $cform = new CForm($cformConfig);

        } else {
            // $config = new Config(array(), 'admin');

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
                    'items' => AdminModule::getLayoutIds(),
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
                    'items' => Hi18nBehavior::getLanguages(),
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

            $cform = new CForm(array(
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
        'items' => Hi18nBehavior::getLanguages(),
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

        if (Yii::app()->request->isPostRequest) {
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
                    Yii::app()->user->setFlash('error', 'При сохранении конфигурации возникли ошибки');
                } else {
                    Yii::app()->user->setFlash('success', 'Конфигурация модуля успешно обновлена.');
                }

                $this->refresh();
            }
        }

        if (isset($_GET['m'])) {
            $this->pageTitle = $newPageTitle = $info->title;
        }

        if ($cform) {
            echo $this->render('CFormUpdate', array(
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
        Yii::app()->user->setFlash('success', 'Кэш и assets были успешно очищены.');
        $this->redirect('/admin/config');
    }

    /**
     * Действие, определяющее наличие модулей в системе
     */
    public function actionModuleDiscover()
    {
        // TODO: убрать отсюда либо в модуль админа, либо в модель Config
        $path = Yii::getPathOfAlias('application.modules');
        $dirs = scandir($path);

        // здесь мы начинаем все сначала, что бы удалялись те модули, которых больше нету в файловой системе
        $modulesInfo = array();

        // старая, сохраненная инфа о модулях
        $oldModulesInfo = $this->modulesInfo;
        $enabledModules = $this->enabledModules;
        $hamsterModules = $this->hamsterModules;

        // добавляем к массиву директорий те модули, которые уже есть в modulesInfo.php
        // это обеспечит нам удаление модулей из конфига, если была удалена их папка, а в конфиге инфа осталась
        $dirs = array_merge($dirs, array_keys($oldModulesInfo), array_keys($enabledModules));
        $dirs = array_unique($dirs);

        foreach ($dirs as $moduleName) {
            if (in_array($moduleName, array('.', '..'))) {
                continue;
            }


            if (is_dir($path . '/' . $moduleName)) {
                $moduleNameForConfig = $moduleName; // FIXME: временно, для обновления конфига
                $modulePath = Yii::getPathOfAlias('application.modules.' . $moduleName);
                if (is_dir($modulePath . '/admin')) {
                    $adminConfig = Config::load($moduleName)->adminConfig;
                    /*if($modulesInfo[$moduleName]['title'] == '')
                    $modulesInfo[$moduleName]['title'] = $adminConfig['title'];
                    else
                    unset($adminConfig['title']);*/

                    $modulesInfo[$moduleName] = $adminConfig;
                    // восстанавливаем версию БД (нам нужна та версия, которая сейчас реально установленна)
                    if (isset($oldModulesInfo[$moduleName]['db']['version']) && !empty($oldModulesInfo[$moduleName]['db']['version'])) {
                        $modulesInfo[$moduleName]['db']['version'] = $oldModulesInfo[$moduleName]['db']['version'];
                    }

                    // восстанавливаем старое имя (на случай, если его менял юзер)
                    if (isset($oldModulesInfo[$moduleName]['title']) && !empty($oldModulesInfo[$moduleName]['title'])) {
                        $modulesInfo[$moduleName]['title'] = $oldModulesInfo[$moduleName]['title'];
                    }

                    $className = ucfirst($moduleName) . 'AdminController';
                    if (file_exists($modulePath . '/admin/' . $className . '.php')) {
                        $enabledModules[$moduleName] = 'application.modules.' . $moduleName . '.admin.' . $className;
                    }

                }
            } else {
                // поудаляем информацию о модуле, если его нету в фс
                unset($enabledModules[$moduleName], $hamsterModules['config']['modules'][$moduleName]);
            }
        }

        $hamsterModules['modulesInfo'] = $modulesInfo;
        $hamsterModules['enabledModules'] = $enabledModules;

        $hamsterModules = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";

        file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $hamsterModules);

        // Обновим статус модуля в конфиге (FIXME: честно говоря грубый способ... но пока так)
        Config::load($moduleName)->save(false);

        Yii::app()->user->setFlash('success', 'Список доступных модулей успешно обновлен. Добавлено модулей: ' . count($modulesInfo));
        $this->redirect('/admin/config');
    }

    /**
     * Включает или выключает модуль, переданный в $_GET['m'], после чего редиректит на /admin/config?m=...
     */
    public function actionSwitchModule()
    {
        $enabledModules = $this->enabledModules;
        $moduleName = $_GET['m'];
        $redirectParams = '';
        if ($moduleName) {
            $moduleAdminPath = Yii::getPathOfAlias('application.modules.' . $moduleName . '.admin');
            if (array_key_exists($moduleName, $enabledModules)) {
                // выключаем модуль
                unset($enabledModules[$moduleName]);
            } else {
                // включаем модуль
                $className = ucfirst($moduleName) . 'AdminController';
                if (file_exists($moduleAdminPath . '/' . $className . '.php')) {
                    $enabledModules[$moduleName] = 'application.modules.' . $moduleName . '.admin.' . $className;
                }

                // проверем базу данных
                $this->testDb($moduleName);

                $redirectParams = '?m=' . $moduleName;
            }

            $hamsterModules = $this->hamsterModules;
            $hamsterModules['enabledModules'] = $enabledModules;

            $configStr = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";
            file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $configStr);

            // Обновим статус модуля в конфиге (FIXME: честно говоря грубый способ... но пока так)
            Config::load($moduleName)->save(false);

            $this->redirect('/admin/config' . $redirectParams);
        }
    }

    /**
     * Метод, восстанавливающий таблицы из дампа в случае,
     * если на этапе активации модуля их не окажется
     *
     * @param mixed $moduleId id модуля, которому принадлежит модель
     * @access public
     * @return void
     */
    protected function testDb($moduleId)
    {
        $tables = Config::load($moduleId)->adminConfig['db']['tables'];
        if (!isset($tables)) {
            return;
        }

        // проверяем, есть ли все таблицы у модуля
        try {
            $db = Yii::app()->db;
            foreach ($tables as $tableName) {
                // запускаем sql комманды
                $db->createCommand('SHOW CREATE TABLE `' . $tableName . '`')->execute();
            }
        } catch (CDbException $e) {
            // одной из таблиц нету - запускаем sql создания таблицы
            if ($moduleId) {
                $path = Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/schema.mysql.sql';
            } elseif ($moduleId == 'admin') {
                $path = Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/' . strtolower($className) . '.schema.mysql.sql';
            } else {
                $path = Yii::getPathOfAlias('application.models._schema') . '/' . strtolower($className) . '.schema.mysql.sql';
            }

            if (is_file($path)) {
                // создаем таблицу в БД
                $sql = file_get_contents($path);
                $db->createCommand($sql)->execute();
                // Пишем в лог
                Yii::log('Создание таблиц для модуля ' . $moduleId, 'info', 'hamster.moduleSwitcher');
            }
        }
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (isset($_POST['ajax']) || isset($_POST['ajaxSubmit']) || isset($_POST['ajaxaction']) || isset($_POST['ajaxIframe']) || Yii::app()->request->isAjaxRequest) {
                echo CJSON::encode(array(
                    'action' => 404,
                    'content' => $error['message'],
                ));
            } else {
                $this->render('error', $error);
            }

        }
    }
}
