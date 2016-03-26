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
 * @var array $batchButtons array with buttons options for batch actions.
 *                          Will be displayed before table
 *
 *
 * @package    hamster.modules.admin.views.admin.table
 */

use KoKoKo\assert\Assert;

//$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
if (!isset($buttons)) {
    $buttons = ['update', 'delete', 'view'];
}

if (!isset($options)) {
    $options = [];
}

if (isset($batchButtons)) {
    Assert::assert($batchButtons, 'batchButtons')->isArray();

    echo '<p>';
    foreach ($batchButtons as $item) {
        echo \CHtml::link(
            $item['label'],
            $this->evaluateExpression($item['url']),
            array_merge(
                ['class' => 'button'],
                isset($item['options']) ? $item['options'] : []
            )
        ) . ' ';
    }
    echo '</p>';
}

if (isset($disableButtons) && $disableButtons) {
    $buttons = [];
} else {
    $defaultButtons = [
        'update' => [
            'url' => '["update", "id" => $data->primaryKey]', // a PHP expression for generating the URL of the button
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_edit.png', // image URL of the button. If not set or false, a text link is used
        ],
        'delete' => [
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_delete.png',
            'url' => '["delete", "id" => $data->primaryKey]',
        ],
        'print' => [
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_print.png',
            'url' => '["print", "id" => $data->primaryKey]',
        ],
        'view' => [
            'url' => 'method_exists($data, "getViewUrl") ? $data->viewUrl : ""',
            'options' => ['target' => '_blank'],
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_view.png',
            'visible' => '!is_object($data) || method_exists($data, "getViewUrl")',
        ],
        'more' => [
            'url' => '["more", "id" => $data->primaryKey]',
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_table.png',
        ],
        'ok' => [
            'url' => '["confirm", "id" => $data->primaryKey]',
            'imageUrl' => $this->adminAssetsUrl . '/images/icon_ok.png',
        ],
    ];

    $buttArr = [];
    $buttCol = [
        'class' => 'CButtonColumn',
        'template' => '',
    ];

    foreach ($buttons as $buttonName => $button) {
        if (is_string($button)) {
            $buttonName = $button;
            $button = [];
        }

        if (isset($defaultButtons[$buttonName])) {
            $button = \CMap::mergeArray(
                $defaultButtons[$buttonName],
                $button
            );
        }

        if (empty($button)) {
            throw new \Exception('Found empty button settings array');
        }

        if (isset($button['options']['ajax']) && $button['options']['ajax'] === true) {
            // подсовываем свой ajax, который возьмет url из ссылки, вместо того, что у yii
            $button['options']['data-ajax'] = '1';
            unset($button['options']['ajax']);
        }

        if (isset($button['options']['confirmation'])) {
            $buttCol['deleteConfirmation'] = $button['options']['confirmation'];
            unset($button['options']['confirmation']);
        }

        $buttArr[$buttonName] = $button;
        $buttCol['template'] .= '{' . $buttonName . '}';
    }

    $buttCol['buttons'] = $buttArr;
}

// Назначаем размер страницы провайдера
$dataProvider->pagination->pageSize = \Yii::app()->params['defaultPageSize'];

// обрабатываем не стандартные типы колонок (или улучшаем стандартные)
foreach ($columns as &$column) {
    if (!is_array($column)) {
        continue;
    }

    if (isset($column['type'])) {
        switch ($column['type']) {
            case 'datetime':
                $column['type'] = 'raw';
                $column['value'] = 'str_replace(" ", "<br />", \Yii::app()->dateFormatter->formatDateTime($data->' . $column['name'] . '))';
                break;
        }
    }

}

$defOpts = array(
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'pager' => array(
        'cssFile' => false,
        'header' => false,
    ),
    'cssFile' => false,
    'beforeAjaxUpdate' => 'startLoad',
    'afterAjaxUpdate' => new \CJavaScriptExpression('function(){stopLoad();reinstallDatePicker();}'),
    'enableHistory' => true,
);

if (isset($buttCol)) {
    $defOpts['columns'][] = $buttCol;
}

if (isset($preTable)) {
    echo $preTable;
}

$gridOptions = \CMap::mergeArray(
    $defOpts,
    $options
);

$grid = $this->widget('zii.widgets.grid.CGridView', $gridOptions);

// Script that reinitialises events on datepicker fields and sets deffault localisation
// for this feature all parameters of datepicker must be set in 'defaultOptions'
// and field with datepicker must be of class reinstallDatePicker
// TODO: убрать отсюда в \admin\components\grid\DateTimeColumn
\Yii::app()->clientScript->registerScript('re-install-date-picker', '
function reinstallDatePicker() {
    $(".reinstallDatePicker").each(function(){$(this).datepicker($.datepicker.regional["' . \Yii::app()->language . '"])});
}
');
\Yii::app()->clientScript->registerScript('data-ajax', <<<SCRIPT
    $('body').on('click', 'a[data-ajax]', function(event) {
        event.preventDefault();

        $.ajax({
            type: 'get',
            url: this.pathname+this.search
        }).always(function() {
            jQuery('#{$grid->id}').yiiGridView('update');
        });
    });
SCRIPT
);
