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
  // переменная, в которой хранится конфиг текущего модуля
  protected $_config;
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
  // массив с настройками модулей Hamster
  protected $_hamsterModules = array();
  // массив с настройками модуля, для которого построена модель
  protected $_curModConfig;
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
  public function __construct($config, $moduleId, $scenario='') 
  {
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
      $this->att2CFormConfig($name, $params);
      $this->hamsterConfigSchema($name, $params);
    }
    
    // парсим настройки hamster, где находится секция для глобальных параметров
    if(is_array($this->_config['hamster']['global']))
      foreach($this->_config['hamster']['global'] as $name => $params)
      {
        $this->att2CFormConfig($name, $params);
        $this->hamsterConfigSchema($name, $params, true);
      }
      
    // добавим в массив с настройками еще параметры, которые передаются модулю
    if(is_array($this->_config['hamster']['options']))
      $this->_curModConfig['modules'][$this->moduleId] = CMap::mergeArray($this->_curModConfig['modules'][$this->moduleId], $this->_config['hamster']['options']);
      
    // загружаем файл с инфой о модулях hamster и обьединяем их с тем, которые получили после парсинга adminConfig.php
    $hamsterModules = $this->hamsterModules;
      
    // добавляем поля из области admin
    $this->addConfigFields(array(
      'adminTitle' => array(
        'label' => 'Название модуля в админ панели',
        'default' => isset($this->_config['hamster']['admin']['title']) ? $this->_config['hamster']['admin']['title'] : $this->moduleId,
        'type' => 'text',
        'linkTo' => &$this->_curModConfig['modulesInfo'][$this->moduleId]['title'],
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
    
    $this->_hamsterModules = CMap::mergeArray($this->_curModConfig, $hamsterModules);
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
      $this->_attributes[] = $fieldId;
      $this->_attLabels[$fieldId] = $fieldOptions['label'];
      if(!empty($fieldOptions['default']))
        $this->_attValsDef[$fieldId] = $fieldOptions['default'];
        
      $this->_attVals[$fieldId] = '';
      if(isset($fieldOptions['linkTo']))
        $fieldOptions['linkTo'] = &$this->_attVals[$fieldId];
      else
        $this->_curModConfig['modules'][$this->moduleId]['params'][$fieldId] = &$this->_attVals[$fieldId];
      $this->_CFormConfig[$fieldId] = array('type' => 'text');
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
    // configuration for different field types
    switch($params['type'])
    {
      case 'email':
        $this->_rules['email'][] = $name;
        $params['type'] = 'text';
      break;
      case 'fieldset':
        foreach($params['elements'] as $subName => $subParams)
          $elements[$subName] = $this->att2CFormConfig($subName, $subParams, true);
        
        $this->_CFormConfig[$name] = array(
          'type' => 'form',
          'title' => $params['title'],
          'elements' => $elements,
          'model' => $this,
        );
        return;
      break;
      case 'number':
        $params['type'] = 'text';
      break;
      case '':
        return;
      break;
    }
    
    $this->_attributes[] = $name;
    
    if(!empty($params['default']))
      $this->_attValsDef[$name] = $params['default'];
    
    if(!empty($params['label']))
      $this->_attLabels[$name] = $params['label'];
    
    $CFormArr = array(
      'type' => $params['type'],
    );
        
    if($return)
      return $CFormArr;
    else
      $this->_CFormConfig[$name] = $CFormArr;
  }
  
  /**
   * Инициализирует массив с значениями по умолчанию и с текущими значениями аттрибутов, а также масив, который потом будет сейвится в конфиг
   *
   * @param string $name имя атрибута
   * @param array $params настройки для текущего элемента конфигурации модуля
   * @param bool $isGlobal флаг, включающий глобальный уровень параметров для конфига Yii
   */
  protected function hamsterConfigSchema($name, $params, $isGlobal = false)
  {
    if($params['type'] == '') return;
    // fieldset должен в конфиге отображаться как вложенные массивы
    if($params['type'] == 'fieldset')
    {
      foreach($params['elements'] as $subName => $subParams)
      {
        $this->_attVals[$subName] = '';
        $attVal[$subName] = &$this->_attVals[$subName];
      }
      // в итоге получится, что в конфиг добавится [$name] и к нему ссылка на массив $attVal (а не ссылка на ссылку на $this->_attVals, как в случае, когда $params['type'] != 'fieldset')
    }else{
      $this->_attVals[$name] = '';
      $attVal = &$this->_attVals[$name];
    }
    if($isGlobal)
    {
      $this->_curModConfig['params'][$name] = &$attVal;
    }else{
      $this->_curModConfig['modules'][$this->moduleId]['params'][$name] = &$attVal;
    }
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
    
    // загружаем файл с настройками hamster и обьединяем их с массивом настроек, за исключением некоторых элементов
    $hamsterConfig = require(Yii::getPathOfAlias('application.modules.admin.config').'/main.php');
    $hamsterConfig['params'] = $this->_hamsterModules['params'];
    unset($hamsterConfig['modules']);
    // добавляем в массив настроек настройки модулей, с учетом их включенности/выключенности в админке
    foreach($this->enabledModules as $moduleId => $moduleInfo)
    {
      if($this->_hamsterModules['modules'][$moduleId])
        $hamsterConfig['modules'][$moduleId] = $this->_hamsterModules['modules'][$moduleId];
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
   * Сокращение для доступа к элементу массива $this->_config['hamster']['admin']['adminPageTitle']
   * @return название модуля для админки
   */
  public function getDefAdminTitle()
  {
    return $this->_config['hamster']['admin']['title'];
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