<?php

$this->pageTitle = 'Логи';

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'columns'=>array(
      array(
        'name' => '1',
        'header' => 'Дата',
        'value' => 'Yii::app()->dateFormatter->formatDateTime($data[1])',
      ),
      array(
        'name' => '2',
        'header' => 'Уровень',
        'cssClassExpression' => '"level_" . $data[2]',
      ),
      array(
        'name' => '3',
        'header' => 'Категория',
      ),
      array(
        'name' => '4',
        'header' => 'Сообщение',
        'value' => '"<pre style=\"height:100px;overflow-y:auto;\">".CHtml::encode(wordwrap($data[4]))."</pre>"',
        'type' => 'raw',
      ),
    ),
    'cssFile'=>false,
    'ajaxUpdate' => false,
    'pager'=>array(
      'cssFile'=>false,
      'header'=>false,
    ),
));
?>