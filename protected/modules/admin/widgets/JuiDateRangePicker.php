<?php
/**
 * Displays a widget to select a date range from/to
 */

namespace admin\widgets;

class JuiDateRangePicker extends \CInputWidget
{
    public function run()
    {
        \Yii::app()->controller->widget('\admin\widgets\JuiDatePicker', [
            'id' => $this->attribute . uniqid(),
            'model' => $this->model,
            'attribute' => $this->attribute, // TODO: _from
        ]);

        \Yii::app()->controller->widget('\admin\widgets\JuiDatePicker', [
            'id' => $this->attribute . uniqid(),
            'model' => $this->model,
            'attribute' => $this->attribute, // TODO: _to
        ]);
    }
}
