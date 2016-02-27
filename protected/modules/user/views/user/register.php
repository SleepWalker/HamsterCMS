<?php

$this->breadcrumbs=array(
	$this->pageTitle,
);
$form = array(
  'buttons'=>array(
    'submit'=>array(
      'type'=>'submit',
      'label'=>'Готово!',
      'attributes' => array(
        'class' => 'submit',
        /*'ajax' => array(
          'type' => 'POST', //request type
          'url' => $this->actionPath.$this->crudid, //url to call.
          //'update' => '#content', //selector to update
          'beforeSend' => 'startLoad',
          'complete' => 'stopLoad',
          'data' => 'js:jQuery(this).parents("form").serialize()+"&ajaxSubmit=1"',
          //'success' => 'function (data) {alert(data)}',
          'error' => 'function(xhr, textStatus, errorThrown){alert(jQuery.parseJSON(xhr.responseText).content)}',
          'cahe' => false,
        ),
        'live'=>false, // Отключаем live event*/
      ),
    )
  ),
  'activeForm'=>array(
    'enableAjaxValidation'=>true,
    'enableClientValidation'=>true,
  ),
);

parseCFormElements($form, $model, $this);

$form['enctype'] = 'multipart/form-data';
$form['model'] = $model;

$form = new CForm($form);
echo '<div class="form registerForm">';
echo '<h1>' . $this->pageTitle . '</h1>';
echo '<p class="note">Поля помеченные <span class="required">*</span> обязательны.</p>';
echo $form;
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
      $fieldType = $fieldType[0];
    }

    $fieldParams['type'] = $fieldType;


    if(strpos($fieldType, "html:") === 0)// html строка
    {
      $form['elements'][]  = substr($fieldType, 5);
      $form['elements'][] = '<p>';
      continue;
    }

    $form['elements'][$fieldName] = $fieldParams;
  }
}
?>
