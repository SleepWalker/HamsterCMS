<?php

/**
 * This is the model class for managing config files of models for Hamster
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Config extends CFormModel
{
  //TODO: loadCForm()
  // в этот массив парсятся все настройки для текущего модуля (из configSchema)
  // далее в него добавляются остальные настройки из application.config.hamsterModules
  protected $_curModConfig;
  // массив с настройками модулей Hamster (application.config.hamsterModules)
  // в конце этот массив будет сливаться с массивом настроек сайта application.config.hamster
  protected $_hamsterModules = array();
  // переменная, в которой хранится конфиг текущего модуля (application.modules.{moduleId}.admin.configSchema)
  protected $_config;
  // индикатор, говорящий, что массив {@link _curModConfig} оуже обьединен с {@link _hamsterModules}
  protected $_isMerged;
  // массив настроек для класса CForm
  protected $_CFormConfig;
  // экземпляр класса CForm
  protected $_CForm;
  // массив с именами аттрибутов
  protected $_attLabels;
  // массив с правилами для аттрибутов модели
  protected $_rules;
  // массив с безопастными атрибутами
  protected $_attributes = array();
  // массив с значениями аттрибутов
  protected $_attVals = array();
  // массив с значениями аттрибутов
  protected $_attValsDef = array();
  // id модуля для которого строится модель
  protected $_moduleId;
  
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
      array(implode(',', $this->_attributes), 'safe'),
    );
	}

	/**
   * NOTE: Этот массив, в принципе, не обязательно использовать, так как CForm может брать значения label прямо из массива с настройками
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return $this->_attLabels;
	}
  
  /**
   * @param string $moduleId название модуля, для которого будет генерироваться модель конфига
   * @param string $scenario name of the scenario that this model is used in. See {@link CModel::scenario} on how scenario is used by models.
   */
  public function __construct(array $config, $moduleId, $scenario='') 
  {
    if(empty($moduleId))
      throw new CException('moduleId не может быть пустой строкой');
    // проверяем можем ли мы писать в необходимых директориях
    $path = Yii::getPathOfAlias('application.config') . '/hamster.php';
      if(!is_writable($path))
      {
        Yii::app()->user->setFlash('error', "Файл '$path' не доступен для записи.");
      }
    $path = Yii::getPathOfAlias('application.config') . '/hamsterModules.php';
      if(!is_writable($path))
      {
        Yii::app()->user->setFlash('error', "Файл '$path' не доступен для записи.");
      }
    $this->_moduleId = $moduleId;
    $this->_config = $config;
    parent::__construct($scenario);
  }
  
  /**
   * Статический метод, который проверяет существует у модуля $moduleId конфиг и создает для него модель
   * @param string $moduleId название модуля, для которого будет генерироваться модель конфига
   * @return mixed модель конфига или null в случае, если у этого модуля нету конфига
   */
  static function load($moduleId)
  {
    $config = Yii::getPathOfAlias('application.modules.'.$moduleId.'.admin').'/configSchema.php';
    if(file_exists($config))
      return new Config(require($config), $moduleId);
    else
      return null; // у этого модуля нету конфига
  }
  
  /**
   * Парсит конфиг и заполняет модель информацией
   */
  public function init()
  {
    // Настройки на уровне модуля
    foreach($this->_config as $name => $params)
    {
      $this->hamsterConfigSchema($name, $params);
    }
    
    // парсим настройки hamster, где находится секция для глобальных параметров
    if(is_array($this->_config['hamster']['global']))
      foreach($this->_config['hamster']['global'] as $name => $params)
      {
        $this->hamsterConfigSchema($name, $params, true);
      }
      
    // добавим в массив с настройками еще параметры, которые передаются модулю
    // TODO: Обязательно написать об этом в будущей документации, бо я сам чуть не забыл о такой возможности
    if(is_array($this->_config['hamster']['options']))
      $this->_curModConfig['config']['modules'][$this->moduleId] = CMap::mergeArray($this->_curModConfig['config']['modules'][$this->moduleId], $this->_config['hamster']['options']);
    
    if($this->moduleId != 'admin') //все настройки для админки генерируются из AdminController
    {
      // добавляем поля из области admin
      $this->addConfigFields(array(
        'adminTitle' => array(
          'label' => 'Название модуля в админ панели',
          'default' => isset($this->_config['hamster']['admin']['title']) ? $this->_config['hamster']['admin']['title'] : $this->moduleId,
          'type' => 'text',
          'linkTo' => '$modulesInfo[$this->moduleId]["title"]',
        ),
      ));

      // добавляем поля url и имя модуля, которые будут отображаться на сайте
      if(!$this->_config['hamster']['admin']['internal'])
      {
        if(!isset($this->_config['moduleName']))
          $this->addConfigFields(array(
            'moduleName' => array(
              'label' => 'Название модуля',
              'type' => 'text',
              'default' => ucfirst($this->moduleId),
            ),
          ));

        if(!isset($this->_config['moduleUrl']))
          $this->addConfigFields(array(
            'moduleUrl' => array(
              'label' => 'URI Адрес модуля',
              'type' => 'text',
              'default' => $this->moduleId,
            ),
          ));
      }
    }
  }
  
  /**
   * Adds new field or fields in config
   *
   * Example of usage:
   * $this->addConfigField(array(
   *    'adminTitle' => array(
   *      'label' => 'Название модуля в админ панели',
   *      'default' => $this->_config['hamster']['admin']['title'],
   *      'type' => 'text',
   *    ),
   *    'adminTitle2' => array(
   *      'label' => 'Название модуля в админ панели',
   *      'default' => $this->_config['hamster']['admin']['title'],
   *      'type' => 'text',
   *    ),
   *  ));
   *
   * Also you can specify option 'linkTo' that to link field value not to module params.
   *
   * @param array $options field options
   */
  public function addConfigFields($options)
  {
    foreach($options as $fieldId => $fieldOptions)
    {
      if(isset($fieldOptions['linkTo']))
      {
        $linkTo = $fieldOptions['linkTo'];
        unset($fieldOptions['linkTo']);
      }

      $this->hamsterConfigSchema($fieldId, $fieldOptions, $linkTo);
    }
  }

  /**
   * Парсит текущий элемент конфига и по его параметрам добавляет в конфиг CFrom новый элемент
   *
   * @param string $name имя атрибута
   * @param array $params настройки для текущего элемента конфигурации модуля
   * @param bool $return если - true, то функция вернет обработанные данные, вместо того, что бы добавлять их в {@link _CFormConfig}
   */
  protected function att2CFormConfig($name, $params, $return = false)
  {  
    // добавляем аттрибут в модель, что бы можно было получить доступ к текущему полю конфига
    if(!$return) $this->setModelAttribute($name, $params);

    // configuration for different field types
    switch($params['type'])
    {
      case 'email':
        $this->_rules['email'][] = $name;
      break;
      case 'fieldset':
        // имя для элемента формы (мы генерируем имена таким образом, что бы они соответствовали структуре массива конфига)
        $curAttName = $return ? $return . '[' . $name . ']' : $name;
        foreach($params['elements'] as $subName => $subParams)
        {
          $subAttName = $subParams['type'] != 'fieldset' ? $curAttName . '[' . $subName . ']' : $subName;
          $elements[$subAttName] = $this->att2CFormConfig($subAttName, $subParams, $curAttName);
        }
        
        $CFormArr = array(
          'type' => 'form',
          'title' => $params['title'],
          'elements' => $elements,
          'model' => $this,
        );

        if(!$return) 
          $this->_CFormConfig[$name] = $CFormArr;
  
        return $CFormArr;
      break;
      case 'number':
        // TODO: разобраться как можно фильтровать float
        $params['type'] = 'text';
      break;
      case '':
        return;
      break;
    }

    if(isset($params['default']))
    {
      if(isset($params['label']))
        $params['label'] .= ' (По умолчанию: ' . $params['default'] . ')';
      unset($params['default']);
    }

    // на данном этапе в массиве $params должны оставаться только параметры для CForm
    $CFormArr = $params;
        
    if($return)
      return $CFormArr;
    else
      $this->_CFormConfig[$name] = $CFormArr;
  }

  // public setModelAttribute(stringname,arrayparams) {{{ 
  /**
   * Добавляет в модель новый аттрибут
   * 
   * @param string $name 
   * @param array $params 
   * @access public
   * @return void
   */
  public function setModelAttribute($name, $params = false)
  {
    $this->_attributes[] = $name;
    
    if(!empty($params['label']))
    {
      $this->_attLabels[$name] = $params['label'];
    }
    
    // обработка вложенных формы (type='fieldset')
    // здесь нам нужно рекурсивно собрать все значения по умолчанию
    if($params['type'] == 'fieldset')
    {
      $parseDefaults = function($params) use(&$parseDefaults) {
        if(is_array($params['elements']))
        {
          foreach($params['elements'] as $subName => $subParams)
          {
            if($subParams['type'] == 'fieldset')
              $subParams['default'] = $parseDefaults($subParams);
            $params['default'][$subName] = $subParams['default'];
          }
          return $params['default'];
        }
      };

      $params['default'] = $parseDefaults($params);
    }

    if(!empty($params['default'])) {
      $this->_attValsDef[$name] = $params['default'];
    }
  }
  // }}}
  
  /**
   * Инициализирует массив с значениями по умолчанию и с текущими значениями аттрибутов, а также масив, который потом будет сейвится в конфиг
   *
   * @param string $name имя атрибута
   * @param array $params настройки для текущего элемента конфигурации модуля
   * @param bool $linkTo определяет к какому полю в конфиге будет привязываться аттрибут. Можно передать global, что бы привязать к глобальным параметрам или переменную
   */
  protected function hamsterConfigSchema($name, $params, $linkTo = false)
  {  
    if($name == 'hamster') return false;
    if($params['type'] == '') throw new CException("У параметра $name не указан обязательный параметр type");

    // Добавляем поле в конфиг CForm
    $this->att2CFormConfig($name, $params);

    $this->_attVals[$name] = '';

    // данные поля или полей
    $attVal = &$this->_attVals[$name];

    if($linkTo == 'global') // вяжем к глобальным параметрам Yii
    { 
      $this->_curModConfig['config']['params'][$name] = &$attVal;
    } elseif($linkTo) { // вяжем еще куда-то
      /*FIXME: if(strpos($linkTo, 'params') == 9 && 0)
      {
        foreach($params['elements'] as $name => $devNull)
        {
          $attVal[$name] = '';
          $this->_curModConfig['config']['params'][$name] = &$attVal[$name];
        }
        return;
      }*/
      $linkTo = strtr($linkTo, array(
        '$config' => '$this->_curModConfig["config"]',
        '$modulesInfo' => '$this->_curModConfig["modulesInfo"]',
      ));

      eval($linkTo . ' = &$attVal;');
    }else{ // вяжем в локальные параметры модуля
      $this->_curModConfig['config']['modules'][$this->moduleId]['params'][$name] = &$attVal;
    }
  }

  /**
   * Переопределяем стандартную функцию, что бы она расспознавала аттрибуты-массивы 
   * 
   * @param mixed $attribute 
   * @access public
   * @return void
   */
  public function isAttributeSafe($attribute)
  {
    if(($pos = strpos($attribute, '[')) !== false)
      $attribute = substr($attribute, 0, $pos);
    return parent::isAttributeSafe($attribute);
  }
  
  /**
   * Переопределяем магический метод __get Yii, что бы можно было обращаться к свойствам, указанным в {@link _config}
   * @param string $name the property name or the event name
   * @return mixed
   */
  public function __get($name)
  {
    if(in_array($name, $this->_attributes))
    {
      $att = $this->_attVals[$name];
      if(($pos = strpos($name, '[')) !== false)
      {
        $name = substr($name, 0, $pos);
      }
      if($this->_attVals[$name] != '')
        return $this->_attVals[$name];
      if(isset($this->_attValsDef[$name]))
        return $this->_attValsDef[$name];
      return '';
    }
    else
      return parent::__get($name);
  }
  
  /**
   * Переопределяем магический метод __set Yii, что бы можно было менять свойства, указанным в {@link _config}
   * @param string $name the property name or the event name
   * @return mixed
   */
  public function __set($name,$value) 
  {
    if(in_array($name, $this->_attributes))
      $this->_attVals[$name] = $value;
    else
      return parent::__set($name,$value);
  }
  
  /**
   * @return array массив с настройками для елемента 'elements' класса CForm
   */
  public function getCForm()
  {
    // сливаем конфиги
    $this->mergeConfigs();

    if(!$this->_CForm && $this->_CFormConfig)
    {
      $this->_CForm = new CForm(array(
        'buttons'=>array(
          'submit'=>array(
            'type'=>'submit',
            'label'=>'Сохранить',
            'attributes' => array(
              'class' => 'submit',
              'id' => 'submit',
            ),
          )
        ),
        'elements' => $this->_CFormConfig,
        'model' => $this,
      ));
    }
    return $this->_CForm;
  }
  
  /**
   * Сохраняет модель
   *
   * @param bool $revalidate маркер, включающий/выключающий повторную валидацию, по умолчанию true
   * @return bool результат сохранения
   */
  public function save($revalidate = true)
  {
    if($revalidate)
      if(!$this->validate())
        return false;

    // сливаем конфиги
    $this->mergeConfigs();
    
    // загружаем файл с настройками hamster и обьединяем их с массивом настроек, за исключением некоторых элементов
    $hamsterConfig = require(Yii::getPathOfAlias('application.modules.admin.config').'/main.php');
    $hamsterConfig = CMap::mergeArray($hamsterConfig, $this->_hamsterModules['config']);
    unset($hamsterConfig['modules']); // удаляем всю старую информацию о модулях
    // добавляем в массив настроек настройки модулей, с учетом их включенности/выключенности в админке
    foreach($this->enabledModules as $moduleId => $moduleInfo)
    {
      if($this->_hamsterModules['config']['modules'][$moduleId])
        $hamsterConfig['modules'][$moduleId] = $this->_hamsterModules['config']['modules'][$moduleId];
      else
        $hamsterConfig['modules'][] = $moduleId;
    }
      
    // активируем админский модуль по дефолту
    $hamsterConfig['modules'][] = 'admin';

    ob_start();
    ?>
if(isset($_SERVER['REQUEST_URI']))
  $GLOBALS['_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
if(isset($_SERVER['REMOTE_ADDR']))
  $GLOBALS['_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];    
    <?php
    $configHeader = ob_get_clean();

    $hamsterConfigStr = "<?php\n\n" . $configHeader . "\n\nreturn " . var_export($hamsterConfig, true) . ";";
    
    //FIXME тут есть пару костылей... для тех случаев, когда надо включать в экспорт php выражения
    //FIXME надо бы придумать более адекватное добавление переменных ГЛОБАЛ
    // удаляем последствия var_export, которая подобавляла индексы к массивам
    $hamsterConfigStr = preg_replace('/[0-9]+ => /', '', $hamsterConfigStr);
    $hamsterConfigStr = preg_replace("/'phpexpr\:([^']+)'/", '$1', $hamsterConfigStr);    
    
    $hamsterModulesStr = "<?php\n\nreturn " . var_export($this->_hamsterModules, true) . ";";

    return (file_put_contents(Yii::getPathOfAlias('application.config') . '/hamster.php', $hamsterConfigStr) !== false)
    && (file_put_contents(Yii::getPathOfAlias('application.config') . '/hamsterModules.php', $hamsterModulesStr) !== false);
  }

  /**
   * Заполняет массивы актуальными данными из уже существующих конфигов
   * 
   * @access protected
   * @return void
   */
  protected function mergeConfigs()
  {
    if(!$this->_isMerged)
    {
      // загружаем файл с инфой о модулях hamster и обьединяем их с тем, которые получили после парсинга adminConfig.php
      // после этой операции {@link _curModConfig} тоже заполнится актуальными данными из конфига
      $hamsterModules = $this->hamsterModules;

      $this->_hamsterModules = CMap::mergeArray($this->_curModConfig, $hamsterModules);

      // сохраним в эту переменную изначальную версию элемента конфига params
      // это нужно для того, что бы пофиксить баг оверврайта полностью всех глобальных параметров при сохранении основных настроек цмс (это из-за того, что там linkTo => '$config["params"]'. 
      // То есть при отправке формы ее данные заменят полностью все глобальные параметры, а нам этого не надо
      $this->_isMerged = is_array($hamsterModules['config']['params']) ? $hamsterModules['config']['params'] : true;
    }

    // нам надо добавить в массив те эллементы из массива params, которые были затерты оверврайтом при сейве основных настроек
    if(is_array($this->_isMerged) && is_array($this->_curModConfig['config']['params']))
    {
      $params = $this->_isMerged;
      $elementsToAdd = array_diff(array_keys($params), array_keys($this->_curModConfig['config']['params']));
      foreach($elementsToAdd as $elementName)
      {
        $this->_curModConfig['config']['params'][$elementName] = $params[$elementName];
      }
    }
  }
  
  /**
   * Загружает настройки модулей Hamster
   * @return array массив с настройками
   */
  public static function hamsterModules()
  {
    $file = Yii::getPathOfAlias('application.config') . '/hamsterModules.php';

    return file_exists($file) ? require($file) : array();
  }
  
  public function getHamsterModules()
  {
    if(!$this->_hamsterModules)
      $this->_hamsterModules = self::hamsterModules();
    return $this->_hamsterModules;
  }
  
  /**
   * @return array массив с информацией о модулях
   */
  public function getModulesInfo()
  {
    return  is_array($this->hamsterModules['modulesInfo']) ? $this->hamsterModules['modulesInfo'] : array();
  }
  
  /**
   * @return array массив с информацией об активных модулях
   */
  public function getEnabledModules()
  {
    return is_array($this->hamsterModules['enabledModules']) ? $this->hamsterModules['enabledModules'] : array();
  }
  
  /**
   * @return string id модуля, для которого построена модель
   */
  public function getModuleId()
  {
    return $this->_moduleId;
  }

  /**
   * Возвращает массив конфигурации с информацией для админ панели
   * 
   * @access public
   * @return array
   */
  public function getAdminConfig()
  {
    return $this->_config['hamster']['admin'];
  }

  public function setDbVersion($v)
  {
    //FIXME это временное решение, для того, что бы можно было изменить версию базы данных
    $this->hamsterModules;
    $this->_hamsterModules['modulesInfo'][$this->moduleId]['db']['version'] = $v;
  }
    
  /**
   *  @return bool маркер, говорящий, новая ли это запись
   */
  public function getIsNewRecord()
  {
    return false;
  }
  
  /**
	 * Сохраняем загруженное изображение и заполняем модель оставшимися данными
	 */
	protected function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      if($this->isNewRecord)
      {
        
      }
      return true;
    }
    else
      return false;
	}
}
