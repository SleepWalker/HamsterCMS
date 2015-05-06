<?php

echo CHtml::beginForm('', 'post', [
    'target' => 'mpdf',
    'id' => 'js-mpdf-form',
]);

echo CHtml::textArea('html', '<h1>Hello World</h1>');

echo CHtml::submitButton();

echo CHtml::endForm();

\Yii::app()->clientScript->registerScript(__FILE__, <<<SCRIPT
    $(function() {
        $('#js-mpdf-form').submit();
    });
SCRIPT
);
?>

<iframe name="mpdf" style="width:100%;height:600px"></iframe>
