<?php
/**
 *  Вьюха, отображающая данные модели в виде таблицы
 *
 *  Параметры:
 *  string $preTable - строка, которая будет печататься перед таблицей
 *  bool $disableButtons - отключает все кнопки
 *  array $buttons - массив, в котором находятся настройки кнопок (см. CGridView 'buttons')
 *  так же в этой вьюхе есть несколько стандартных кнопок: update, delete, create, more, view, ok
 *  настройки этих кнопок можно переопределять, передавая массив с ихним именем, к примеру:
 *  ...
 *  'buttons' => array(
 *    'more' => array(
 *      'visible' => '$data->hasMore',
 *    ),
 *  ),
 *  ...
 *  
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.views.admin.table
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
//$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
if(!isset($buttons)) 
  $buttons = array('update', 'delete', 'view');
  
if(!isset($options)) 
  $options = array();
  
if(isset($disableButtons) && $disableButtons) 
  $buttons = array();
else
{
  $updateButton = array(  
    'update'=>array(
      //'label'=>'...',     // text label of the button
      //'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'update/" . $data->primaryKey)',       // a PHP expression for generating the URL of the button
      'url'=>'"'. $this->actionPath . 'update/" . $data->primaryKey',       // a PHP expression for generating the URL of the button
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_edit.png',  // image URL of the button. If not set or false, a text link is used
      //'options'=>array(), // HTML options for the button tag
      //'click'=>'...',     // a JS function to be invoked when the button is clicked
      //'visible'=>'',   // a PHP expression for determining whether the button is visible
    )
  );
  
  $deleteButton = array(
    'delete'=>array(
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_delete.png',
      'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'delete/" . $data->primaryKey)',
      'url'=>'"' . $this->actionPath . 'delete/" . $data->primaryKey',
    )
  );
  
  $printButton = array(
    'print'=>array(
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_print.png',
      'url'=>'"' . $this->actionPath . 'print/" . $data->primaryKey',
    )
  );
  
  $viewButton = array(
    'view'=>array(
      'url'=>'method_exists($data, "getViewUrl") ? $data->viewUrl : ""',
      'options'=>array(
        'target'=>'_blank',
      ),
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_view.png',
      'visible'=>'method_exists($data, "getViewUrl")',
    )
  );
  
  $moreButton = array(
    'more'=>array(
      'url'=>'"' . $this->actionPath . 'more/" . $data->primaryKey',
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_table.png',
    )
  );

  $okButton = array(
    'ok'=>array(
      'url'=>'"' . $this->actionPath . 'confirm/" . $data->primaryKey',
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_ok.png',
    )
  );
  
  $buttArr = array();
  $buttCol = array(
    'class'=>'CButtonColumn',
  );
  
  $buttCol['template'] = '';
  foreach($buttons as $buttonName => $button)
  {
    $curButtonSettings = array();
    if(is_array($button))
    {
      $curButtonSettings[$buttonName] = $button;
      if(is_array(${$buttonName . 'Button'}))
      {
        $curButtonSettings[$buttonName] = array_merge(
          ${$buttonName . 'Button'}[$buttonName],
          $curButtonSettings[$buttonName]
        );
      }
      $button = $buttonName;
    }else
      $curButtonSettings = ${$button . 'Button'};
    
    $buttArr = array_merge(
      $buttArr,
      $curButtonSettings
    );
    $buttCol['template'] .= '{' . $button . '}';
  }
  
  $buttCol['buttons'] = $buttArr;
}

// Назначаем размер страницы провайдера
$dataProvider->pagination->pageSize = Yii::app()->params['defaultPageSize'];

// обрабатываем не стандартные типы колонок (или улучшаем стандартные)
foreach($columns as &$column)
{
  if(!is_array($column))
    continue;

  if(isset($column['type']))
    switch($column['type'])
    {
    case 'datetime':
      $column['type'] = 'raw';
      $column['value'] ='str_replace(" ", "<br />", Yii::app()->dateFormatter->formatDateTime($data->' . $column['name'] . '))';
      break;
    }
}


$defOpts = array(
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
  'pager'=>array(
    'cssFile'=>false,
    'header'=>false,
  ),
  'cssFile'=>false,
  'beforeAjaxUpdate' => 'startLoad',
  'afterAjaxUpdate' => new CJavaScriptExpression('function(){stopLoad();reinstallDatePicker();}'),
  'enableHistory' => true,
  //'ajaxUpdate' => false,
);

if(isset($buttCol))
  $defOpts['columns'][] = $buttCol;

if(isset($preTable))
  echo $preTable;

$this->widget('zii.widgets.grid.CGridView', array_merge(
  $defOpts,
  $options
));

// Script that reinitialises events on datepicker fields and sets deffault localisation
// for this feature all parameters of datepicker must be set in 'defaultOptions'
// and field with datepicker must be of class reinstallDatePicker
Yii::app()->clientScript->registerScript('re-install-date-picker', '
function reinstallDatePicker() {
    $(".reinstallDatePicker").each(function(){$(this).datepicker($.datepicker.regional["' . Yii::app()->language . '"])});
}
');
?>
