<?php
// TODO: префиксные индексы для увеличения быстродействия: http://www.xaprb.com/blog/2009/02/12/5-ways-to-make-hexadecimal-identifiers-perform-better-on-mysql/
// Полезная информация по интернационализации: http://blog.mozilla.org/webdev/2007/04/18/teaching-cakephp-to-be-multilingual-part-3/
// TODO: поле local можно будет конвертировать в ENUM
class Hi18nBehavior extends CActiveRecordBehavior
{
  // Поля модели, которые должны локализироваться
  public $i18nAtts;

  // id модуля, используется для того, что бы обеспечить уникальность идентификаторов полей
  public $moduleId = 'root';

  // Поле с выбором языка для формы редактирования в админке
  public $i18nlang;

  // текущий язык пользователя
  protected $_language;

  protected $_localizable = true;

  /**
   * Добавляет join на локализированные поля
   * @param model $owner 
   */
  public function attach($owner)
  {
    parent::attach($owner);
    if(Yii::app()->params['i18n']['enabled'] != true)
    {
      // отключаем интернационализацию, если она не включена в настройках цмс
      // отключаем поведение
      $this->enabled = false;
      return;
    }

    if($module = Yii::app()->controller->module)
      $curModuleId = $module->id;

    // приобразовываем аттрибуты модели, которые были переданы поведению
    if(is_string($this->i18nAtts))
      if(strpos($this->i18nAtts, ',') !== false)
        $this->i18nAtts = preg_split('/ ?, ?/', $this->i18nAtts);
      else
        $this->i18nAtts = array($this->i18nAtts);
    
    if(!$owner->isNewRecord) 
    {
      // мы не будем предлогать переводить материал, который еще не добавлен на оригинальном языке
      // добавляем парочку безопасных аттрибутов
      $validators = $owner->getValidatorList();

      $validator = CValidator::createValidator('safe', $owner, 'i18nlang');
      $validators->add($validator);
    }
   

    $localeId = $this->language;
    if(isset($_POST['i18n']['language']))
      $localeId = $_POST['i18n']['language'];
    if(isset($_POST[get_class($this->owner)]['i18nlang']))
      $localeId = $_POST[get_class($this->owner)]['i18nlang'];
    if($pos = (strpos($localeId, '_')) !== false)
      $fbLocaleId = substr($localeId, 0, $pos);
    else
      $fbLocaleId = $localeId;
    //$fbLocaleId .= '_' . $fbLocaleId;

    //TODO: возможность задать fallback localeID
    if(empty($localeId) || $fbLocaleId == Yii::app()->sourceLanguage || !in_array($fbLocaleId, Yii::app()->params['i18n']['languages']))
    {
      // отключаем интернационализацию, если язык пользователя совпадает с языком приложения или приложение не знает такого языка
      $this->_localizable = false;
    }

    // админку и все запросы в ней мы оставляем на русском языке, 
    // кроме случая, когда идет запрос на поля с переводом 
    // (выпадающая меню с выбором языка на странице редактирования материала)
    if(isset($_POST['i18n']) || !empty($_POST[get_class($model)]['i18nlang']))
      $this->_localizable = true;
    elseif($curModuleId == 'admin')
      $this->_localizable = false;


    foreach($this->i18nAtts as $attribute)
    {
      $name = 'tr_' . $attribute;
      $field_id = array(
        strtolower($this->moduleId),
        strtolower(get_class($owner)),
        $attribute,
      );
      $params = array($owner::BELONGS_TO, 'I18n', 'id',
        'on' => "`{$name}`.hash=UNHEX(:{$name}_hash2)",// пока будем использовать только первую часть параметра локали, тоесть ru, en, ua и т.д. `{$name}`.hash=UNHEX(:{$name}_hash1) OR 
        'params' => array(
          //":{$name}_hash1" => md5(implode('.', $field_id) . ".{$localeId}"),
          ":{$name}_hash2" => md5(implode('.', $field_id) . ".{$fbLocaleId}"),
        ),
      );
      $owner->metaData->addRelation($name, $params);

      if($this->_localizable)
        $owner->with($name);
    }
  }

  public function handleEditRequest()
  {
    $language = $_POST['i18n']['language'];

    // FIXME: js сделан не очень качественно. нуждается в рефакторинге

    echo '$(".i18nField").each(function() {
      var obj = $(this); 
      // для редактор js
      if(obj.parents(".redactor_box").length > 0) obj = obj.parents(".redactor_box").eq(0);
      obj.remove();
    });';
    if(empty($_POST['i18n']['language'])) Yii::app()->end();

    foreach($this->i18nAtts as $attribute)
    {
      echo $this->getHtmlField($attribute, $language);
    }
    
    Yii::app()->end();
  }

  protected function getHtmlField($attribute, $language)
  {
      switch($this->owner->fieldTypes[$attribute])
      {
      case 'text':
        $method = 'TextField';
        break;
      case 'textarea':
        $method = 'TextArea';
        $js = '$(".field_' . $attribute . '").append(' . CJavaScript::encode('<style type="text/css">.redactor_box{float:left;width:49%}</style>') . ');$("#' . get_class($this->owner) . '_tr_' . $attribute . '_translation").redactor({"focus":false,"removeClasses":false,"imageUpload":"\/admin\/imageupload","imageGetJson":"\/admin\/uploadedimages","lang":"ru","toolbar":"default"});';

        break;
      }
      $method = 'active' . $method;

      if(isset($method))
        $formFieldJs = CJavaScript::encode(CHtml::$method($this->owner, 'tr_' . $attribute . '[translation]', array('class' => 'i18nField')));

      return '$(".field_' . $attribute . ' div.errorMessage").before(' . $formFieldJs . ');'.$js;
  }
/*
  public function beforeFind(CEvent $event)
  {
    $criteria = $event->sender->getCDbCriteria();
    foreach($this->i18nAtts as $attribute)
    {
      $name = 'tr_' . $attribute;
      $cirteria->with[] = $name;
    }
  }
 */
  public function afterFind(CEvent $event)
  {
    $model = $event->sender;
    // создаем модели в тех полях, которые оказались пустыми
    foreach($this->i18nAtts as $attribute)
    {
      $translation = $model->{'tr_' . $attribute};
      if( ! ($translation instanceof I18n))
      {
        $model->{'tr_' . $attribute} = new I18n;
      }
    }

    if(isset($_POST['i18n']))
    {
      $this->handleEditRequest();
      // нам надо было, что бы произошел join запроса. далее можно не выполнять действия связанные с {@link $_localizable}
      $this->_localizable = false;
    }

    if($this->_localizable && !isset($_POST[get_class($model)]['i18nlang']))
    { 
     $model = $event->sender;
      foreach($this->i18nAtts as $attribute)
      {
        $translation = $model->{'tr_' . $attribute}->translation;
        if(!empty($translation))
        {
          $model->{$attribute} = $translation;
        }
      }
    }
  }

  public function afterSave(CEvent $event)
  {
    $model = $event->sender;
    $language = $_POST[get_class($model)]['i18nlang'];

    if(empty($language)) return;

    foreach($this->i18nAtts as $attribute)
    {
      $translation = $model->{'tr_' . $attribute};
      if( ! ($translation instanceof I18n)) continue;
      $translation->attributes = $_POST[get_class($model)]['tr_' . $attribute];
      if($translation->isNewRecord)
      {
      $field_id = array(
        strtolower($this->moduleId),
        strtolower(get_class($this->owner)),
        $attribute,
      );
        $translation->attributes = array(
          'locale' => $language,
          'field_id' => implode('.', $field_id),
          'hash' => new CDbExpression("x'" . md5(implode('.', $field_id) . ".{$language}") . "'"),
          'id' => $model->primaryKey,
        );
      }
      $translation->save();     
    }
  }

  public function getLanguage()
  {
    if(empty($this->_language))
      $this->_language = isset(Yii::app()->request->cookies['myLang']) ? Yii::app()->request->cookies['myLang']->value : '';

    return $this->_language;
  }

  public function getLangField()
  {
    return array(
      'dropdownlist',
      'label'=>'Интернационализировать',
      'items'=>self::getLanguages(),
      'attributes' => array(
        'ajax' => array(
          'complete' => new CJavaScriptExpression('function(xhr) {eval(xhr.responseText)}'),
          'type' => 'POST',
          'data' => new CJavaScriptExpression(CJavaScript::encode(array(
            'i18n[language]' => new CJavaScriptExpression('$(this).val()'),
          ))),
        ),
        'empty'=>'--Выберите язык--',
      ),
    );
  }

  public static function getLanguages()
  {
    return array(
      'en' => 'Английский',
      'ua' => 'Украинский',
    );
  }

  public static function onBeginRequest($event)
  {
    if(Yii::app()->params['i18n']['enabled'] == true)
    {
      $uri = trim($_SERVER['REQUEST_URI'], '/');

      $pos = strpos($uri, '/');
      if($pos === false) // главная страница
        $language = $uri;
      else
        $language = substr($uri, 0, $pos);

      if(in_array($language, Yii::app()->params['i18n']['languages'])) 
      {
        $_SERVER['REQUEST_URI'] = str_replace($language . '/', '', $_SERVER['REQUEST_URI']);
      }else{
        // если в uri нету инфы о языке, ставим дефолтный язык в куки
        $language = Yii::app()->sourceLanguage;
      }

      $ref = Yii::app()->request->urlReferrer;
      // если юзер пришел извне, переадресуем его на подходящую для него страницу
      if(empty($ref) || strpos($ref, Yii::app()->request->hostInfo) === false)
      {
        $lang = isset(Yii::app()->request->cookies['myLang']) ? Yii::app()->request->cookies['myLang']->value : '';
        if(empty($lang))
          $lang = Yii::app()->request->preferredLanguage;

        if(in_array($lang, Yii::app()->params['i18n']['languages']) && $lang != $language)
          Yii::app()->request->redirect('/' . $lang . Yii::app()->request->requestUri, true);
      }

      Yii::app()->language = $language;

      Yii::app()->request->cookies['myLang'] = new CHttpCookie('myLang', $language, array('expire'=>time() + (20 * 365 * 24 * 60 * 60)));
    }
  }
}
