<?php

echo CHtml::beginForm('', 'post', [
    'target' => 'code',
    'id' => 'js-code-form',
]);

echo CHtml::textArea('code', '<h1>Hello World</h1>');

echo CHtml::submitButton();

echo CHtml::endForm();

\Yii::app()->clientScript->registerScript(__FILE__, <<<SCRIPT
    $(function() {
        $('#js-code-form').submit();
    });
SCRIPT
);
?>

<iframe name="code" style="width:100%;height:600px;background: #fff;"></iframe>
