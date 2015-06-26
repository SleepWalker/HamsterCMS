<?php

namespace admin\components;

class HModuleManager extends \CApplicationComponent
{
    private $_hamsterModules = [];

    /**
     * Проверяет фс на наличие новых модулей
     * в случае если в фс отсутствует модуль присутствующий в конфиге,
     * он будет удален из конфига
     *
     * @return void
     */
    public function discoverModules()
    {
        $path = \Yii::getPathOfAlias('application.modules');
        $dirs = scandir($path);

        // здесь мы начинаем все сначала, что бы удалялись те модули, которых больше нету в файловой системе
        $modulesInfo = array();

        // старая, сохраненная инфа о модулях
        $oldModulesInfo = $this->getModulesInfo();
        $enabledModules = $this->getEnabledModules();
        $hamsterModules = $this->getHamsterModules();

        // добавляем к массиву директорий те модули, которые уже есть в modulesInfo.php
        // это обеспечит нам удаление модулей из конфига, если была удалена их папка, а в конфиге инфа осталась
        $dirs = array_merge($dirs, array_keys($oldModulesInfo), array_keys($enabledModules));
        $dirs = array_unique($dirs);

        foreach ($dirs as $moduleName) {
            if (in_array($moduleName, array('.', '..'))) {
                continue;
            }

            if (is_dir($path . '/' . $moduleName)) {
                $modulePath = \Yii::getPathOfAlias('application.modules.' . $moduleName);
                if (is_dir($modulePath . '/admin')) {
                    $adminConfig = \admin\models\Config::load($moduleName)->adminConfig;
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
                }
            } else {
                // поудаляем информацию о модуле, если его нету в фс
                unset($enabledModules[$moduleName], $hamsterModules['config']['modules'][$moduleName]);
            }
        }

        $hamsterModules['modulesInfo'] = $modulesInfo;
        $hamsterModules['enabledModules'] = $enabledModules;

        $this->updateConfig($hamsterModules);
    }

    /**
     * @param  string $moduleName id модуля
     * @throws Exception IF can't find module controller
     * @throws Exception IF can't save config
     * @return void
     */
    public function switchModule($moduleId)
    {
        $enabledModules = $this->getEnabledModules();
        if ($this->isModuleEnabled($moduleId)) {
            // выключаем модуль
            unset($enabledModules[$moduleId]);
        } else {
            // включаем модуль
            $moduleDirAlias = 'application.modules.' . $moduleId . '.admin';
            $moduleAdminPath = \Yii::getPathOfAlias($moduleDirAlias);
            $className = ucfirst($moduleId) . 'AdminController';
            if (file_exists($moduleAdminPath . '/' . $className . '.php')) {
                $enabledModules[$moduleId] = $moduleDirAlias . '.' . $className;
                $this->testDb($moduleId);
            } else {
                throw new \Exception("Не могу найти админ контроллер модуля $moduleId");
            }
        }

        $hamsterModules = $this->getHamsterModules();
        $hamsterModules['enabledModules'] = $enabledModules;

        $this->updateConfig($hamsterModules);
    }

    /**
     * @param  string  $moduleId
     * @return boolean wheter module is enabled
     */
    public function isModuleEnabled($moduleId)
    {
        $enabledModules = $this->getEnabledModules();

        return isset($enabledModules[$moduleId]);
    }

    /**
     * Загружает настройки модулей Hamster
     * @return array массив с настройками
     */
    public function getHamsterModules()
    {
        if (!$this->_hamsterModules) {
            $this->_hamsterModules = \admin\models\HArrayConfig::load()->hamsterModules;
        }

        return $this->_hamsterModules;
    }

    /**
     * @return array массив с информацией о модулях
     */
    public function getModulesInfo()
    {
        $hamsterModules = $this->getHamsterModules();
        return isset($hamsterModules['modulesInfo']) && is_array($hamsterModules['modulesInfo']) ? $hamsterModules['modulesInfo'] : [];
    }

    /**
     * @return array массив с информацией об активных модулях
     */
    public function getEnabledModules()
    {
        $hamsterModules = $this->getHamsterModules();
        return isset($hamsterModules['enabledModules']) && is_array($hamsterModules['enabledModules']) ? $hamsterModules['enabledModules'] : [];
    }

    /**
     * @param  array $hamsterModules массив с актуальным конфигом модулей
     *
     * @throws Exception IF can't save config
     * @return void
     */
    private function updateConfig(array $hamsterModules)
    {
        $configStr = "<?php\n\nreturn " . var_export($hamsterModules, true) . ";";
        if (!file_put_contents(\Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $configStr)) {
            throw new \Exception('Не удалось сохранить настройки модулей');
        }

        // Обновим статус модуля в конфиге (FIXME: честно говоря грубый способ... но пока так)
        $moduleIds = array_values(array_flip($hamsterModules['enabledModules']));
        \admin\models\Config::load(reset($moduleIds))->save(false);
        $this->_hamsterModules = $hamsterModules;
    }

    /**
     * Метод, восстанавливающий таблицы из дампа в случае,
     * если на этапе активации модуля их не окажется
     *
     * @param string $moduleId id модуля, которому принадлежит модель
     * @return void
     */
    private function testDb($moduleId)
    {
        // TODO: needs rewriting or should be deleted after refactoring
        $tables = \admin\models\Config::load($moduleId)->adminConfig['db']['tables'];
        if (!isset($tables)) {
            return;
        }

        // проверяем, есть ли все таблицы у модуля
        try {
            $db = \Yii::app()->db;
            foreach ($tables as $tableName) {
                // запускаем sql комманды
                $db->createCommand('SHOW CREATE TABLE `' . $tableName . '`')->execute();
            }
        } catch (CDbException $e) {
            // одной из таблиц нету - запускаем sql создания таблицы
            if ($moduleId) {
                $path = \Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/schema.mysql.sql';
            } elseif ($moduleId == 'admin') {
                $path = \Yii::getPathOfAlias('application.modules.' . $moduleId . '.admin') . '/' . strtolower($className) . '.schema.mysql.sql';
            } else {
                $path = \Yii::getPathOfAlias('application.models._schema') . '/' . strtolower($className) . '.schema.mysql.sql';
            }

            if (is_file($path)) {
                // создаем таблицу в БД
                $sql = file_get_contents($path);
                $db->createCommand($sql)->execute();
                // Пишем в лог
                \Yii::log('Создание таблиц для модуля ' . $moduleId, 'info', 'hamster.moduleSwitcher');
            }
        }
    }
}
