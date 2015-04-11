<?php
/**
 * Extends default CJuiDatePicker by providing default settings for all datepickers
 * in admin module
 */

namespace admin\widgets;

\Yii::import('zii.widgets.jui.CJuiDatePicker', true);

class JuiDatePicker extends \CJuiDatePicker
{
    public $defaultOptions = [
        'showOn' => 'focus',
        'showOtherMonths' => true,
        'selectOtherMonths' => true,
        'changeMonth' => true,
        'changeYear' => true,
        'showButtonPanel' => true,
        'autoSize' => true,
        'dateFormat' => "yy-mm-dd",
    ];

    public function init()
    {
        $this->language = \Yii::app()->language;

        parent::init();
    }
}
