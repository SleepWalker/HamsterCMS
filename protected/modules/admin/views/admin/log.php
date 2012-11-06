<?php

$this->pageTitle = 'Логи';

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'filter'=>$filtersForm,
    'columns'=>array(
      array(
        'name' => '1',
        'header' => 'Дата',
        'value' => 'Yii::app()->dateFormatter->formatDateTime($data[1])',
        'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
              'model'=> $filtersForm, 
              'attribute'=>'1', 
              'language' => Yii::app()->language,
              'defaultOptions' => array(  
                'showOn' => 'focus', 
                'showOtherMonths' => true,
                'selectOtherMonths' => true,
                'changeMonth' => true,
                'changeYear' => true,
                'showButtonPanel' => true,
                'autoSize' => true,
                'dateFormat' => "yy/mm/dd",
              ),
              'options' => array(
                'dateFormat' => "yy/mm/dd",
              ),
            ), true),
      ),
      array(
        'name' => '2',
        'header' => 'Уровень',
        'filter' => array(
          'error' => 'error',
          'warning' => 'warning',
          'info' => 'info',
          'trace' => 'trace',
          'profile' => 'profile',
        ),
        'cssClassExpression' => '"level_" . $data[2]',
      ),
      array(
        'name' => '3',
        'header' => 'Категория',
        'filter' => $categories,
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
