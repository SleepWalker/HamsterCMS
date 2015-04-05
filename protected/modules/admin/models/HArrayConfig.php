<?php
/**
 * Модель для управления текстовыми файлами конфигурации Hamster
 *
 * @author     Sviatoslav Danylenko <dev@udf.su>
 * @package    hamster.modules.admin.models
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\models;

use \CComponent;
use \CFormModel;
use \CHtml;
use \CMap;
use \Exception;
use \Yii;

class HArrayConfig extends CComponent
{
    protected $_hamsterModules = array();
    protected $_partialForms = array();

    protected function __construct()
    {
        $configPath = Yii::getPathOfAlias('application.config');
        if (!is_writable($configPath)) {
            Yii::app()->user->setFlash('error', "Директория '$configPath' не доступна для записи.");
        }

        $hamsterPath = $configPath . '/hamster.php';
        if (is_file($hamsterPath) && !is_writable($hamsterPath)) {
            Yii::app()->user->setFlash('error', "Файл '$hamsterPath' не доступен для записи.");
        }

        $hamsterModules = $configPath . '/hamsterModules.php';
        if (file_exists($hamsterModules)) {
            $this->_hamsterModules = require $hamsterModules;

            if (is_file($hamsterModules) && !is_writable($hamsterModules)) {
                Yii::app()->user->setFlash('error', "Файл '$hamsterModules' не доступен для записи.");
            }
        }
    }

    /**
     * Создает экземпляр класса настроек
     */
    public static function load()
    {
        return new HArrayConfig();
    }

    /**
     * Генерирует заново конфиг hamster.php
     */
    public static function rebuild()
    {
        return HArrayConfig::load()->save();
    }

    /**
     * Сохраняет конфиг hamsterModules и генерирует конфиг hamster
     */
    public function save()
    {
        $hamster = $this->mergeConfigs();

        $hamsterStr = $this->serializeConfig($hamster, array(
            'readOnly' => true,
            'phpExpr' => true,
            'beforeReturn' => "Yii::setPathOfAlias('hamster', dirname(dirname(__FILE__)));\n",
        ));

        $hamsterModulesStr = $this->serializeConfig($this->_hamsterModules);

        return (file_put_contents(Yii::getPathOfAlias('application.config') . '/hamster.php', $hamsterStr) !== false)
        && (file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $hamsterModulesStr) !== false);
    }

    /**
     * Проходится по всем созданным моделям и переносит их значения
     * в конфиг HArrayConfig::_hamsterModules
     * Обьединяет конфиги hamsterModules и main и возвращает массив для hamster.php
     *
     *    @return array hammster.php конфиг
     */
    public function mergeConfigs()
    {
        if (count($this->_partialForms)) {
            // сливаем полученные параметры со всех форм
            foreach ($this->_partialForms as $form) {
                $form->save();
            }
        }

        $main = require Yii::getPathOfAlias('application.modules.admin.config') . '/main.php';
        $hamster = CMap::mergeArray($main, $this->_hamsterModules['config']);

        // удаляем всю информацию о модулях, что бы добавить конфиг только для тех, что включены
        unset($hamster['modules']);
        foreach ($this->enabledModules as $moduleId => $moduleInfo) {
            if (isset($this->_hamsterModules['config']['modules'][$moduleId])) {
                $hamster['modules'][$moduleId] = $this->_hamsterModules['config']['modules'][$moduleId];
            } else {
                $hamster['modules'][] = $moduleId;
            }

        }

        // активируем админский модуль по дефолту
        $hamster['modules'][] = 'admin';

        return $hamster;
    }

    /**
     * Компилирует массив конфига в php код
     * @param array $config
     * @param array $params дополнительные параметры (key => val), такие как:
     *    readOnly - Добавляет комментарий в код, что этот файл сгенерирован автоматически
     *    phpExpr - конвертирует все значения массивов, начинающиеся с 'phpexpr:' в php код
     */
    public function serializeConfig($config, $params = array())
    {
        $str = "<?php\n\n";

        if (isset($params['beforeReturn'])) {
            // костыль для тех случаев, когда надо включать в экспорт php выражения
            $str .= $params['beforeReturn'] . "\n\n";
        }

        $str .= "return " . var_export($config, true) . ";";

        // удаляем последствия var_export, которая подобавляла индексы к массивам
        $str = preg_replace('/[0-9]+ => /', '', $str);

        if (isset($params['phpExpr']) && $params['phpExpr']) {
            // костыль для тех случаев, когда надо включать в экспорт php выражения
            $str = preg_replace("/'phpexpr\:([^']+)'/", '$1', $str);
        }

        if (isset($params['readOnly']) && $params['readOnly']) {
            // добавляем предупреждение, что этот конфиг только для чтения
            $str = preg_replace("/^<\?php/", "<?php\n\n//ВНИМАНИЕ!!!\n//Этот конфиг генерируется автоматически, не стоит его менять врнучную", $str, 1);
        }

        return $str;
    }

    public function getHamsterModules()
    {
        return $this->_hamsterModules;
    }

    public function getModulesInfo()
    {
        return $this->_hamsterModules['modulesInfo'];
    }

    /**
     * Включает модуль CMS
     * @param string $moduleId id модуля
     * @param boolean $autoSave флаг, включающий автоматическое сохранение конфига
     */
    public function enableModule($moduleId, $autoSave = true)
    {
        $this->_hamsterModules['enabledModules'][$moduleId] = 'application.modules.' . $moduleId . '.admin.' . ucfirst($moduleId) . 'AdminController';

        if ($autoSave) {
            $this->save();
        }

    }

    /**
     * Выключает модуль CMS
     * @param string $moduleId id модуля
     * @param boolean $autoSave флаг, включающий автоматическое сохранение конфига
     */
    public function disableModule($moduleId, $autoSave = true)
    {
        unset($this->_hamsterModules['enabledModules'][$moduleId]);

        if ($autoSave) {
            $this->save();
        }

    }

    public function getEnabledModules()
    {
        return $this->_hamsterModules['enabledModules'];
    }

    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (Exception $e) {
            $result = $this->getMagicCallDestination($name);

            if ($result !== false) {
                return $result;
            } else {
                throw $e;
            }

        }
    }

    public function __call($name, $parameters)
    {
        $result = $this->getMagicCallDestination($name, $parameters);

        return $result !== false ? $result : parent::__call($name, $parameters);
    }

    public function __set($name, $value)
    {
        $result = $this->getMagicCallDestination($name, array($value), 'set');

        return $result !== false ? $result : parent::__set($name, $value);
    }

    /**
     * Функция, которая занимается обслуживанием сразу __get, __set, __call методов
     *
     * Примеры синтаксиса:
     * $config->params ($config->getParams(), $config->setParams($val), $config->params = $val)
     * $config->[moduleId]Params;
     * $config->root; $config->[moduleId]Root;
     * $config->[moduleId]Info;
     * $config->[componentId]Component;
     *
     * $config->[params|root|info|component]Model(array([CForm params])) - CFormModel модель заполненная значениями
     *           из соответствующего места конфига. (@see HPartialConfig::getCFormConfig)
     *
     * @param string $name имя метода/свойства
     * @param array $parameters параметры для функции
     * @param $type тип get/set
     */
    protected function getMagicCallDestination($name, $parameters = array(), $type = 'get')
    {
        $tmpType = substr($name, 0, 3);

        if (in_array($tmpType, array('get', 'set'))) {
            $type = $tmpType;
            $name = str_replace($type, '', $name);
        }

        if ($type == 'set' && count($parameters) != 1) {
            throw new CException('Для вызова set-метода необходим один входной параметр, а было дано:' . count($parameters));
        }

        if (preg_match('/^(?<partialId>\w*)(?<configPartial>params|info|model|component|root)$/i', strtolower($name), $matches)) {
            switch ($matches['configPartial']) {
                case 'params':    // params конфига или модуля
                    if (empty($matches['partialId'])) {
                        $target = &$this->_hamsterModules['config']['params'];
                    } elseif (isset($this->_hamsterModules['config']['modules'][$matches['partialId']]['params']) || ($type == 'set' && is_array(($this->_hamsterModules['config']['modules'][$matches['partialId']]['params'] = array())))) {
                        $target = &$this->_hamsterModules['config']['modules'][$matches['partialId']]['params'];
                    } else {
                        return array();     // должно возвращаться только при get
                    }
                    break;
                case 'root':    // корень конфига или настроек модуля
                    if (empty($matches['partialId'])) {
                        $target = &$this->_hamsterModules['config'];
                    } elseif (isset($this->_hamsterModules['config']['modules'][$matches['partialId']]) || ($type == 'set' && is_array(($this->_hamsterModules['config']['modules'][$matches['partialId']] = array())))) {
                        $target = &$this->_hamsterModules['config']['modules'][$matches['partialId']];
                    } else {
                        return array();     // должно возвращаться только при get
                    }
                    break;
                case 'info':    // возвращает часть modulesInfo, которая расчитана только на настройку модуля admin
                    $info = &$this->_hamsterModules['modulesInfo'];
                    if (!empty($matches['partialId'])) {
                        if (isset($info[$matches['partialId']]) || ($type == 'set' && is_array(($info[$matches['partialId']] = array())))) {
                            $target = &$info[$matches['partialId']];
                        } else {
                            return array();     // должно возвращаться только при get
                        }
                    } else {
                        $target = &$info;
                    }

                    break;
                case 'model':    // Возвращает модель HPartialConfig
                    if (count($parameters) == 0) {
                        throw new CException('Не задан обязательный параметр с конфигом CForm для метода ' . $name);
                    }

                    $key = $matches['partialId'];

                    if (!isset($this->_partialForms[$key])) {
                        $this->_partialForms[$key] = new HPartialConfig($key, $this, $parameters[0]);
                    }

                    // model нельзя сетить, потому сразу возвращаем ее
                    return $this->_partialForms[$key];
                    break;
                case 'component':    // доступ к компонентам приложения
                    if (isset($this->_hamsterModules['config']['components'][$matches['partialId']]) || ($type == 'set' && is_array(($this->_hamsterModules['config']['components'][$matches['partialId']] = array())))) {
                        $target = &$this->_hamsterModules['config']['components'][$matches['partialId']];
                    } else {
                        return array();     // должно возвращаться только при get
                    }
                    break;
            }

            if (isset($target)) {
                if ($type == 'get') {
                    return $target;
                } else {
                    $target = $parameters[0];
                }

                return true;
            }
        }

        return false;
    }
}

class HPartialConfig extends CFormModel
{
    /**
     * @var string $_id идентификатор текущей формы
     */
    protected $_id;

    /**
     * @var HArrayConfig $_rootConfig ссылка на родителя
     */
    protected $_rootConfig;

    /**
     * @var array $_cFormConfig конфиг для CForm
     */
    protected $_cFormConfig;

    /**
     * @var array $_attributes аттрибуты модели
     */
    protected $_attributes = array();

    /**
     * @var array $_safeAttributeAlias массив, который будет хранить ключи типа 'i18n[languages]', по которым CForm
     *         будет проверять безопасность атрибута. В действительности же будут использоваться значения из масивов $_POST['foo']['i18n']['languages']
     */
    protected $_safeAttributeAliases = array();

    public function __construct($id, $rootConfig, $schema, $scenario = '')
    {
        $this->_id = $id;
        $this->_rootConfig = $rootConfig;
        $configValues = $rootConfig->{'get' . $id}();

        // Считываем дефаулт значения параметров, инициализируем аттрибуты и готовим массив настроек CForm
        foreach ($schema as $field => $params) {
            if (($pos = strpos($field, '[')) !== false) {
                // например i18n[languages]
                $fieldKey = $field;
                $fieldKey = '[' . substr_replace($fieldKey, '][', $pos, 1);

                if (($pos = strpos($fieldKey, '[]')) !== false) {
                    $fieldKey = substr($fieldKey, 0, $pos);
                }

                $fieldKey = preg_replace('/\[([^\]]*)\]/', '[\'$1\']', $fieldKey); // Например: ['i18n']['languages']

                // Добавляем алиас в безопасные аттрибуты (биндим новый аттрибут CForm к его аналогу для конфига)
                // например: $this->_attributes['i18n']['languages'] = &$this->_attributes["i18n[languages]"];
                eval('$this->_safeAttributeAliases[$field] = &$this->_attributes' . $fieldKey . ';');
            } else {
                $fieldKey = '[\'' . $field . '\']';
            }

            // вычисление дефолтного значения аттрибута
            eval('
                if(isset($params["default"]))
                    $this->_attributes' . $fieldKey . ' = $params["default"];
                if(isset($configValues' . $fieldKey . ') && !empty($configValues' . $fieldKey . '))
                    $this->_attributes' . $fieldKey . ' = $configValues' . $fieldKey . ';
                elseif(!isset($this->_attributes' . $fieldKey . ')) $this->_attributes' . $fieldKey . ' = null;
                ');

            if (isset($params['default'])) {
                unset($params['default']);
            }

            $this->_cFormConfig[$field] = $params;

        }

        // устанавливаем callback для установки имени модели
        $this->setModelNameConverter();

        parent::__construct($scenario);
    }

    public function rules()
    {
        return array(
            array(implode(',', array_keys($this->_safeAttributeAliases)), 'safe'),
        );
    }

    /**
     * Этот метод позаботится о присваивании всех аттрибутов из переменной HPartialConfig::_attributes
     * При этом будут корректно присваиваться значения переданные массивом
     */
    public function onUnsafeAttribute($name, $value)
    {
        if (!$this->hasAttribute($name)) {
            return;
        }

        if (is_array($value) && is_array($this->$name)) {
            $this->$name = CMap::mergeArray($this->$name, $value);
        } else {
            $this->$name = $value;
        }
    }

    /**
     * Сохраняет аттрибуты формы обратно в конфиг родителя
     */
    public function save()
    {
        $this->_rootConfig->{$this->_id} = CMap::mergeArray($this->_rootConfig->{$this->_id}, $this->_attributes);
    }

    /**
     * Возвращает конфиг для CForm
     */
    public function getCformConfig()
    {
        return $this->_cFormConfig;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getRootConfig()
    {
        return $this->_rootConfig;
    }

    /**
     * Переопределяем стандартную функцию, что бы она расспознавала аттрибуты-массивы
     *
     * @param mixed $attribute
     * @see CModel::getSafeAttributeNames()
     * @access public
     * @return void
     */
    public function isAttributeSafe($attribute)
    {
        if (array_key_exists($attribute, $this->_attributes)) {
            return true;
        }

/*
if(($pos = strpos($attribute, '[')) !== false)
$attribute = substr($attribute, 0, $pos);
 */
        return parent::isAttributeSafe($attribute);
    }

    /**
     * Переопределяем магический метод __get Yii, что бы можно было обращаться к свойствам, указанным в {@link HPartialConfig::_attributes}
     * @param string $name the property name or the event name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            /*
            if(($pos = strpos($name, '[')) !== false)
            {
            $name = substr($name, 0, $pos);
            }
             */

            return $this->_attributes[$name];
        } elseif (isset($this->_safeAttributeAliases[$name])) {
            return $this->_safeAttributeAliases[$name];
        } else {
            return parent::__get($name);
        }

    }

    /**
     * Переопределяем магический метод __set Yii, что бы можно было менять свойства, указанным в {@link HPartialConfig::_attributes}
     * @param string $name the property name or the event name
     * @return mixed
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            return parent::__set($name, $value);
        }

    }

    public function attributeNames()
    {
        return CMap::mergeArray(parent::attributeNames(), array_keys($this->_attributes));
    }

    public function hasAttribute($name)
    {
        return in_array($name, $this->attributeNames());
    }

    /**
     * устанавливает callback для установки имени модели
     */
    public function setModelNameConverter()
    {
        $self = $this;

        CHtml::setModelNameConverter(function ($model) use ($self) {
            return $self->modelNameConverter($model);
        });
    }

    /**
     * Функция для переопределения названия каждой модели
     * @param CModel $model
     */
    public function modelNameConverter($model)
    {
        $name = '';
        if ($model instanceof self) {
            $name = 'HArrayConfig_' . $model->id;
        } else {
            // для всех других форм временно включаем стандартное конвертирование
            CHtml::setModelNameConverter(null);
            $name = CHtml::modelName($model);
            $this->setModelNameConverter();
        }

        return $name;
    }
}

// TODO: готовить main.php к состоянию deprecated
// TODO: Поддержка валидации
/*
// Примерная структура конфигов
hamster.php = admin\config\main.php + hamsterModules.php[config]
hamsterModules.php
config
modules
modulesInfo
moduleId
title
description
db
routes - удобная для формы версия, а в config - удобная для правил URL
enabledModules
moduleId => moduleAdminActionRoute
main.php
modules
admin --- Больше модулей нету! Все настройки модулей в hamsterModules.php
components
...
import
preload
language
...
 */
