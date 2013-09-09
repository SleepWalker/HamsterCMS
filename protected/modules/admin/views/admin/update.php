<?php 
/**
 * Обеспечивает автоматическую генирацию html форм для админки
 *
 * @var CActiverRecord $model модель для которой создается форма
 * @var array $buttons массив с дополнительными кнопками формы
 * @var array $elements массив с настройками дополнительных полей формы
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.views.admin.update
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

$form = array(
  'buttons'=>CMap::mergeArray(
      array(
      'submit'=>array(
        'type'=>'submit',
        'label'=> is_subclass_of($model, 'CFormModel') ? 'Отправить' : $model->isNewRecord ? 'Добавить' : 'Сохранить',
        'attributes' => array(
          'class' => 'submit',
          'id' => 'submit',
        ),
      )
    ),
    isset($buttons) ? $buttons : array()
  ),
  'id' => $this->action->id . 'Form',
  'activeForm'=>array(
    'enableAjaxValidation'=>true,
    'enableClientValidation'=>false,
    'clientOptions' => array(
      'validateOnSubmit' => true,
      'validateOnChange' => true,
      // обработчики события afterValidate должны возвращать false, в том случае, если валидация прошла успешно
      'afterValidate' => 'js:function(form, data, hasError) {var afterValidateHasErrors = $("body").triggerHandler("afterValidate", [form, data, hasError]);return (!hasError && !afterValidateHasErrors)}',
    ),
  ),
  'enctype' => 'multipart/form-data',
);

parseCFormElements($form, $model, $this);

if(isset($elements))
  $form['elements'] = CMap::mergeArray($form['elements'], $elements);

$form = new CForm($form, $model);
echo '<div class="form">';
echo $form->render();
echo '</div>';



function parseCFormElements(&$form, $model, $controller)
{
  foreach ($model->fieldTypes as $fieldName => $fieldType)
  { 
    $fieldValue = isset($model->attributes[$fieldName]) ? $model->attributes[$fieldName] : null;

    $fieldParams = ''; // Очищаем переменную от старых значений
    
    if (is_array($fieldType))
    {
      $fieldParams = $fieldType; // настройки поля, к примеру items для dropdownlist
      unset($fieldParams[0]);
      $fieldType = isset($fieldType['type']) ? $fieldType['type'] : $fieldType[0];
    }
    
    $fieldParams['type'] = $fieldType;
    
    if ($fieldType == 'translit' || $fieldType == 'translitUrl')
    {
      $controller->widget('application.widgets.translit.TranslitWidget', array(
        'model'=>$model,
        'attribute'=>$fieldName,
        'urlMode' => $fieldType == 'translitUrl',
      ));
      $fieldParams['type'] = 'text';
    }
    
    if (in_array($fieldType, array('autocomplete', 'tags')))
    {
      $additionaryOptions = array();
      if($fieldType == 'tags')
      {
        $additionaryOptions = array(
          'search' => 'js:function() {
            // custom minLength
            var term = this.value.split( /,\s*/ ).pop();
            if ( term.length < 2 ) {
              return false;
            }
          }',
          'focus' => 'js:function() {
            // prevent value inserted on focus
            return false;
          }',
          'select' => 'js:function( event, ui ) {
            var terms = this.value.split( /,\s*/ );
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            // add placeholder to get the comma-and-space at the end
            terms.push( "" );
            this.value = terms.join( ", " );
            return false;
          }',
        );
      }
      // Вторым параметром передаем (captureOutput) true.
      // таким образом мы запустим инициализацию скриптов, но текстовое поле писать не будем,
      // это сделает за нас CForm
      $controller->widget('zii.widgets.jui.CJuiAutoComplete', array(
        'model'     => $model,
        'attribute' => $fieldName,
        'sourceUrl'=>$controller->curModuleUrl . 'ac'.$fieldName,
        // additional javascript options for the autocomplete plugin
        'options'=>array_merge(
          array(
            'minLength'=>'2',
          ),
          $additionaryOptions
        ),
      ), true);
      $fieldParams['type'] ='text';
    }
    
    if(strpos($fieldType, "html:") === 0)// html строка
    {
      $form['elements'][] = substr($fieldType, 5);
      $form['elements'][] = '<p>';
      continue;
    }
    
    // Textarea более маленького размера и без использования виджета redactorJs
    if ($fieldType == 'textareaTiny')
    {
      $fieldParams['style'] = "width:400px;height:150px;";
      $fieldParams['type'] = 'textarea';
    }

    // textarea для редактирования markdown
    if ($fieldType == 'markdown')
    {
      $fieldParams = array(
          'type' => 'ext.markitup.HMarkitupWidget',
          'theme'=>'hamster',
          //'htmlOptions'=>array('rows'=>15, 'cols'=>70),
          'options'=>array(
            //'previewParserPath'=>Yii::app()->urlManager->createUrl($preview)
          )
        );
    }
    
    // datetime picker
    if ($fieldType == 'datetime')
    {
      $controller->widget(
          'ext.jui.EJuiDateTimePicker',
          array(
              'model'     => $model,
              'attribute' => $fieldName,
              'language'=> 'ru',//default Yii::app()->language
              //'mode'    => 'datetime',//'datetime' or 'time' ('datetime' default)
              'options'   => array(
                  'dateFormat' => 'yy-mm-dd',
                  'addSliderAccess' => 'true',
                  'stepMinute' => 10,
                  //'timeFormat' => '',//'hh:mm tt' default
              ),
          )
      , true);
      $fieldParams['type'] = 'text';
    }
    
    // autocomplete field for yandexMap
    if($fieldType == 'yandexAutoComplete') 
    {
      $controller->widget('application.widgets.yandex.YandexAutoComplete', array(
        'model'     => $model,
        'attribute' => $fieldName,
        'latitudeAtt' => 'latitude',
        'longitudeAtt' => 'longitude',
        // additional javascript options for the autocomplete plugin
        'options'=>array(
          'minLength'=>'2',
        ),
      ), true); // ставим флаг captureOutput, так как нам нужно только подключить события и скрипт
      $fieldParams['type'] ='text';
    }

    if ($fieldType == 'file')
    {
      $fieldParams = array(
        'type' => 'ext.fields.HFileField',
      );
    }
       
    // добавляем элемент в форму
    $form['elements'][$fieldName] = $fieldParams;
    
    if($fieldType == 'form') // мы нашли форму, запускаем рекурсию
    {
      //parseCFormElements($form['elements'][$fieldName], $fieldParams['model'], $controller);
    }
    
    if ($fieldType == 'textarea') // запускаем виз. редактор
      $controller->widget('application.widgets.redactorjs.Redactor', array(
        'editorOptions' => array(
          'fixed' => true,
          'fixedTop' => '39',
          'minHeight' => '300',
          'wym' => true,
          'focus' => false,
          'imageUpload' => '/admin/imageupload',
          'imageGetJson' => '/admin/uploadedimages',
        ),
        'lang' => Yii::app()->language,
        'model' => $model,
        'attribute' => $fieldName,
      ), true);
      
      
    $form['elements'][] = '<p>';
  }

}

ob_start();
?>
// вешаем обработчик на уровень выше, что бы он всегда срабатывал после валидации формы
$('#<?php echo $form['id'] ?>').parent().on('submit.ajaxSubmit', '#<?php echo $form['id'] ?>', function() {
  $.ajax({
    type: 'POST',
    dataType: 'JSON',
    url: $(this).prop('action'),
    beforeSend: startLoad,
    complete: stopLoad,
    data: $(this).serialize()+"&ajaxSubmit=1",
    context: $(this),
    error: function(xhr, textStatus, errorThrown){console.log(jQuery.parseJSON(xhr.responseText).content)},
    success: function (data) {parseAnswer(this, data)},
    cahe: false,
  });
  return false;
});

/**
 *  Обрабатывает ответ сервера
 **/
function parseAnswer($form, answer)
{
  if(!answer) {
    console.log('parseAnswer: No Data');
    return;
  }
  switch(answer.action)
    {
      case 404:
         console.log(answer.content);
      break;
      case 'renewForm':
        $form.parent().replaceWith( answer.content );
        // Перезапускаем транспорт
        //prepareForm();
      break;
      case 'redirect':
        location.href = answer.content;
      break;
    }
}
<?php
$formJs = ob_get_clean();

Yii::app()->getClientScript()->registerScript('formJs', $formJs); 
?>
