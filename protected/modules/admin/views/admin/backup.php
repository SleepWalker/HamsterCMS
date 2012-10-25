<?php
$this->pageTitle = 'Резервные копии';

if(Yii::app()->user->hasFlash('dbbackup'))
  echo Yii::app()->user->getFlash('dbbackup');
      
echo CHtml::beginForm();
echo '<p>' . CHtml::submitButton('Сделать резервную копию') . ' ' . 
  CHtml::submitButton('Очистить базу данных', array('name'=>'flushDb', 'disabled'=>'disabled')) . '</p>';
echo CHtml::endForm();

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'columns'=>array(
      array(
        'name' => 'name',
        'header' => 'Имя файла',
      ),
      array(
        'name' => 'size',
        'header' => 'Размер',
        'value' => 'formatSize($data["size"])',
      ),
      array(
        'name' => 'time',
        'header' => 'Дата',
        'value' => 'Yii::app()->dateFormatter->formatDateTime($data["time"])',
      ),
      array(
        'name' => 'restore',
        'header' => '',
        'value' => '"<a href=\"?restore=" . $data["name"] . "\" title=\"Восстановить этот бекап\" class=\"icon_refresh\"></a>"',
        'type' => "raw",
      ),
    ),
    'cssFile'=>false,
    'ajaxUpdate' => false,
    'pager'=>array(
      'cssFile'=>false,
      'header'=>false,
    ),
));

function formatSize($value, $decimals = 2, $base = 1024)
{
  $units=array('B','KB','MB','GB','TB');
  for($i=0; $base<=$value; $i++) $value=$value/$base;
  return round($value, $decimals).$units[$i];
}