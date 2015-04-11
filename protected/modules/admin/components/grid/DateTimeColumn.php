<?php
/**
 * The column that properly handles date data and provides corresponding filter
 */
namespace admin\components\grid;

class DateTimeColumn extends \CDataColumn
{
    public $type = 'datetime';

    public function init()
    {
        $this->filter = \Yii::app()->controller->widget('\admin\widgets\JuiDateRangePicker', [
            'model' => $this->grid->dataProvider->model,
            'attribute' => $this->name,
        ], true);

        parent::init();
    }
}
