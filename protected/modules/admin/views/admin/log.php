<?php

$this->pageTitle = 'Логи';

$globalFilters = '<span class="filters">';
$globalFilters .= CHtml::dropDownList(
    'log',
    isset($_GET['log']) ? $_GET['log'] : '',
    $availableLogs,
    ['style' => 'margin: 10px 0;']
);
$globalFilters .= '</span>';

$this->widget('zii.widgets.grid.CGridView', [
    'dataProvider' => $dataProvider,
    'filter' => $filtersForm,
    'summaryText' => $globalFilters . Yii::t('zii', 'Displaying {start}-{end} of {count} result(s).'),
    'columns' => [
        [
            'name' => '1',
            'header' => 'Дата',
            'value' => 'Yii::app()->dateFormatter->formatDateTime($data[1])',
            'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', [
                'model' => $filtersForm,
                'attribute' => '1',
                'language' => Yii::app()->language,
                'defaultOptions' => [
                    'showOn' => 'focus',
                    'showOtherMonths' => true,
                    'selectOtherMonths' => true,
                    'changeMonth' => true,
                    'changeYear' => true,
                    'showButtonPanel' => true,
                    'autoSize' => true,
                    'dateFormat' => "yy/mm/dd",
                ],
                'options' => [
                    'dateFormat' => "yy/mm/dd",
                ],
            ], true),
        ],
        [
            'name' => '2',
            'header' => 'Уровень',
            'filter' => [
                'error' => 'error',
                'warning' => 'warning',
                'info' => 'info',
                'trace' => 'trace',
                'profile' => 'profile',
            ],
            'cssClassExpression' => '"level_" . $data[2]',
        ],
        [
            'name' => '3',
            'header' => 'Категория',
            'filter' => $categories,
        ],
        [
            'name' => '4',
            'header' => 'Сообщение',
            'value' => '"<pre style=\"height:100px;overflow-y:auto;\">".CHtml::encode(wordwrap($data[4]))."</pre>"',
            'type' => 'raw',
        ],
    ],
    'cssFile' => false,
    'ajaxUpdate' => false,
    'pager' => [
        'cssFile' => false,
        'header' => false,
    ],
]);
