<?php 
$form = array(
  'buttons'=>array(
    'submit'=>array(
      'type'=>'submit',
      'label'=>$model->isNewRecord ? 'Добавить' : 'Сохранить',
      'attributes' => array(
        'class' => 'submit',
        'id' => 'submit',
      ),
    )
  ),
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

$form['model'] = $model;

$form = new CForm($form);
echo '<div class="form">';
echo $form;
echo '</div>';



function parseCFormElements(&$form, $model, $controller)
{
  foreach ($model->fieldTypes as $fieldName => $fieldType)
  { 
    $fieldValue = $model->attributes[$fieldName];

    $fieldParams = ''; // Очищаем переменную от старых значений
    
    if (is_array($fieldType))
    {
      $fieldParams = $fieldType; // настройки поля, к примеру items для dropdownlist
      unset($fieldParams[0]);
      $fieldType = $fieldType[0];
    }
    
    $fieldParams['type'] = $fieldType;
    
    if ($fieldType == 'translit')
    {
      $controller->widget('application.widgets.translit.TranslitWidget', array(
        'model'=>$model,
        'attribute'=>$fieldName,
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
      $form['elements'][]  = substr($fieldType, 5);
      $form['elements'][] = '<p>';
      continue;
    }
    
    // Textarea более маленького размера и без использования виджета redactorJs
    if ($fieldType == 'textareaTiny')
    {
      $fieldParams['style'] = "width:400px;height:150px;";
      $fieldParams['type'] = 'textarea';
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
    if($fieldType == 'yandexAutoComplete') {
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
       
    $form['elements'][$fieldName] = $fieldParams;
    
    if($fieldType == 'form') // мы нашли форму, запускаем рекурсию
    {
      //parseCFormElements($form['elements'][$fieldName], $fieldParams['model'], $controller);
    }

    if ($fieldType == 'file' && $model[$fieldName] != '' && !is_array($model[$fieldName]))
    {// Выводим картинку (только в случае если картинка одна, тоесть атрибут модели не содержит массив)
      $form['elements'][$fieldName]['style'] = 'display:none;';
      $form['elements'][] = CHtml::image($model->uploadsUrl . $model[$fieldName], $fieldName, array('id'=>$fieldName.'_tag'));
      $form['elements'][] = CHtml::link(CHtml::image($controller->adminAssetsUrl.'/images/icon_delete.png','Удалить картинку'), '#', array('id'=>'renewImage'));
      Yii::app()->clientScript->registerCoreScript('jquery');
      Yii::app()->getClientScript()
      ->registerScript('renewImage','jQuery("#renewImage").bind("click", function() {
          $(this).remove();
          $("#' . $fieldName.'_tag").remove();
          $("#yt' . get_class($model).'_'.$fieldName . '").val("delete");
          with($("#' . get_class($model).'_'.$fieldName . '")) 
          {
            css({display:"block"});
            after("Изображение окончательно удалится/изменится после отправки формы");
          }
          return false;
        });', CClientScript::POS_END);
    }
    
    if ($fieldType == 'textarea') // запускаем виз. редактор
      $controller->widget('application.widgets.redactorjs.Redactor', array(
        'editorOptions' => array(
          'fixed' => true,
          'focus' => false,
          'removeClasses' => false,
          'imageUpload' => '/admin/imageupload',
          'imageGetJson' => '/admin/uploadedimages',
        ),
        'model' => $model,
        'attribute' => $fieldName,
      ));
      
      
    $form['elements'][] = '<p>';
  }

}

$formJs = <<<EOD
// вешаем обработчик на уровень выше, что бы он всегда срабатывал после валидации формы
$('form#yw0').parent().on('submit.ajaxSubmit', 'form#yw0', function() {
  $.ajax({
    type: 'POST',
    dataType: 'JSON',
    url: $(this).prop('action'),
    beforeSend: startLoad,
    complete: stopLoad,
    data: $(this).serialize()+"&ajaxSubmit=1",
    error: function(xhr, textStatus, errorThrown){console.log(jQuery.parseJSON(xhr.responseText).content)},
    success: function (data) {parseAnswer(data)},
    cahe: false,
  });
  return false;
});
      /*'ajax' => array(
        'type' => 'POST', //request type
        'url' => $this->actionPath.$this->crudid, //url to call.
        'beforeSend' => 'startLoad',
        'complete' => 'stopLoad',
        'data' => 'js:jQuery(this).parents("form").serialize()+"&ajaxSubmit=1"',
        //'success' => 'function (data) {parseAnswer(data)}',
        'error' => 'function(xhr, textStatus, errorThrown){console.log(jQuery.parseJSON(xhr.responseText).content)}',
        'cahe' => false,
      ),
      'live'=>false, // Отключаем live */
/**
 *  Обрабатывает ответ сервера
 **/
function parseAnswer(answer)
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
        $( $('#submit')[0].form.parentNode ).replaceWith( answer.content );
        // Перезапускаем транспорт
        //prepareForm();
      break;
      case 'redirect':
        location.href = answer.content;
      break;
    }
}
EOD;

if (strpos($form, 'type="file"')) 
{
$iframeTransport = <<<EOD
var d = document; 

// Отключаем обработку события отправки формы через AJAX
$('form#yw0').parent().off('submit.ajaxSubmit');

var frame = createTransportFrame();
prepareForm();

/**
 * Готовит форму к iframe транспорту
 **/
function prepareForm(form)
{
  $('form#yw0').prop('target', frame.name);
  
  // вешаем обработчик на уровень выше, что бы он всегда срабатывал после валидации формы
  $('form#yw0').parent().on('submit', 'form#yw0', startLoad);
  
  
  $('#submit').prop('name', 'ajaxSubmit');
  
  $('<input type="hidden" name="ajaxIframe" value="1" />').insertAfter('#submit');
}

/**
 *  Создает iframe для транспорта
 **/
function createTransportFrame() 
{
  // Создаем фрейм, через который мы будем общаться с сервером
  if(document.getElementById('upload_target')) return document.getElementById('upload_target');
  
  var iframe = d.createElement('iframe');
  iframe.name = 'upload_target';
  iframe.id = 'upload_target';
  iframe.style.display = 'none';
  d.body.appendChild(iframe);
  
  iframe.onload = function() 
  {
    // При создании фрейма он загрузится с страницей типа about:blank и создаст событие. Игнорим его
    if(parent.upload_target.location.href == 'about:blank') return;
    
    stopLoad();
    
    // Проверяем, ответил ли сервер. Если ответил, обрабатываем ответ
    var answer = parent.upload_target.document.body.innerHTML;
    if (answer == '') return;
    try
    {
      var JSONanswer = jQuery.parseJSON(answer);
    } catch(e) {
      console.log(e.name + ' : ' + e.message);
      console.log(answer);
      return;
    }
    
    parseAnswer(JSONanswer);
  };
  
  return iframe;
}
EOD;
}
Yii::app()->getClientScript()
      ->registerScript('iframeTransport', $formJs . $iframeTransport); 
?>
