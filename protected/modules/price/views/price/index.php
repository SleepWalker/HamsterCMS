<?php
$this->pageTitle = 'Прайсы';
$this->breadcrumbs = array($this->pageTitle);

echo '<h1>' . $this->pageTitle . '</h1>';
$this->beginAside('', array(
  'title' => 'Фильтр',
  'position' => 'top',
));
echo '<div class="form" id="priceFilter>';
$action = preg_replace('/\?[^\?]*$/','',$_SERVER["REQUEST_URI"]);
$form = $this->beginWidget('CActiveForm', array(
  'id'=>'priceFilterForm',
  'method'=>'get',
  'action'=>$action,
  'enableAjaxValidation'=>false,
  'enableClientValidation'=>false,
)); 

echo '<p class="row" style="margin-top: 0;padding-top: 0;">' . $form->labelEx($prod, 'name') . $form->textField($prod, 'name') . '</p>';
// FIXME: убрано до лучших времен. нужно либо сделать настроку отображения фильтрации 
// по файлам в админке, либо отказаться от этого функционала
// echo '<p class="row">' . $form->labelEx($prod, 'file_id') . $form->dropDownList($prod, 'file_id', $priceChoises) . '</p>';
foreach($cats as $columnId => $cat) 
  echo '<p class="row">' . $form->labelEx($prod, $columnId) . $form->dropDownList($prod, $columnId, $cat) . '</p>';

$this->widget('ext.jui.HFilterRangeSlider', array(
    'model'=>$prod,
    'attribute'=>'min',
    'maxAttribute'=>'max',
    // additional javascript options for the slider plugin
    'options'=>array(
        'range'=>true,
        'min'=>$prod->minValue,
        'max'=>$prod->maxValue,
    ),
));
echo '<p class="row" align="center"><br /><br />' . CHtml::submitButton('Поиск') . '</p>';

$this->endWidget();

echo '</div>';
$this->endAside();
?>
<style>
</style>
<?php
if(count($priceDownloadMenu))
{
?>
<section class="priceDownload">
<a href="" id="priceDownloadLink">Скачать прайсы</a>
<ul>
<?php
  foreach($priceDownloadMenu as $link => $name)
  {
    echo '<li>' . CHtml::link($name, $link) . '</li>';
  }
?>
</ul>
</section>
<?php
} //$priceDownloadMenu

ob_start();
?>
$(function() {
  $('#priceDownloadLink').click(function() {
    $(this).next().show('normal');
    $(this).remove();
    
    return false;
  });
});
<?php
// скрипт отображения списка файлов на закачку
Yii::app()->clientScript->registerScript(__CLASS__, ob_get_clean(), CClientScript::POS_END);

$columns =  array(
  'code',
  'name',
  'price',
);

foreach(array_keys($cats) as $cat)
  $columns[] = str_replace('_id', '', $cat) . 'Name';

if(is_array(($extra = $config['extraLabels'])))
  foreach($extra as $attribute => $name)
    $columns[] = array(
      'name' => $name,
      'value' => '$data->extra["' . $attribute . '"]',
    );

$this->widget('zii.widgets.grid.CGridView', array(
  'dataProvider'=>$dataProvider,
	'columns'=>$columns,
  'pager'=>array(
    'cssFile'=>false,
    'header'=>false,
    'maxButtonCount' => 8,
  ),
  'cssFile'=>false,
  'summaryText' => false,
  'enableHistory' => true,
));
