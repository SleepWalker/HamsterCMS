<?php
    echo '<div class="form hAsideCharFilter" id="priceFilter">';
    $action = preg_replace('/\?[^\?]*$/','',$_SERVER["REQUEST_URI"]);
    $form = $this->beginWidget('CActiveForm', array(
      'id'=>'priceFilterForm',
      'method'=>'get',
      'action'=>$action,
      'enableAjaxValidation'=>false,
      'enableClientValidation'=>false,
    )); 

    echo '<p class="row" style="margin-top: 0;padding-top: 0;">' . $form->labelEx($prod, 'name') . $form->textField($prod, 'name') . '</p>';
    // FIXME: убрано до лучших времен. нужно либо сделать настроку отображения фильтрации 
    // по файлам в админке, либо отказаться от этого функционала
    // echo '<p class="row">' . $form->labelEx($prod, 'file_id') . $form->dropDownList($prod, 'file_id', $priceChoises) . '</p>';
    foreach($cats as $columnId => $cat) 
      echo '<p class="row">' . $form->labelEx($prod, $columnId) . $form->dropDownList($prod, $columnId, $cat) . '</p>';

    $this->widget('ext.jui.HFilterRangeSlider', array(
      'model'=>$prod,
      'attribute'=>'min',
      'maxAttribute'=>'max',
      // additional javascript options for the slider plugin
      'options'=>array(
        'range'=>true,
        'min'=>$prod->minValue,
        'max'=>$prod->maxValue,
      ),
    ));
    echo '<p class="row" align="center"><br /><br />' . CHtml::submitButton('Поиск') . '</p>';

    $this->endWidget();

    echo '</div>';
