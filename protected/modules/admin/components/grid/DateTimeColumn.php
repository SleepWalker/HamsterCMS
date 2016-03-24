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
        if (!($this->grid->dataProvider instanceof \CActiveDataProvider)) {
            throw new \InvalidArgumentException('Grid data provider must be of type CActiveDataProvider');
        }

        $this->filter = \Yii::app()->controller->widget('\admin\widgets\JuiDateRangePicker', [
            'model' => $this->grid->dataProvider->model,
            'attribute' => $this->name,
        ], true);

        parent::init();
    }
}
