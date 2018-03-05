<?php
/**
 * Обеспечивает автоматическую генирацию html форм для админки
 *
 * @var CActiverRecord $model модель для которой создается форма
 * @var array $buttons массив с дополнительными кнопками формы
 * @var array $elements массив с настройками дополнительных полей формы
 */

// TODO: у CForm можно переопределить класс CFormInputElement. Есть смысл переопределить его и расширить с новыми типами полей

$form = array(
    'buttons' => CMap::mergeArray(
        array(
            'submit' => array(
                'type' => 'submit',
                'label' => is_subclass_of($model, 'CFormModel') ? 'Отправить' : $model->isNewRecord ? 'Добавить' : 'Сохранить',
                'attributes' => array(
                    'class' => 'submit',
                    'id' => 'submit',
                ),
            ),
        ),
        isset($buttons) ? $buttons : array()
    ),
    'id' => $this->action->id . 'Form',
    'activeForm' => array(
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            // обработчики события afterValidate должны возвращать false, в том случае, если валидация прошла успешно
            'afterValidate' => 'js:function(form, data, hasError) {var afterValidateHasErrors = $("body").triggerHandler("afterValidate", [form, data, hasError]);return (!hasError && !afterValidateHasErrors)}',
        ),
    ),
    'enctype' => 'multipart/form-data',
);

$params = get_defined_vars();
$params['controller'] = $this;
parseCFormElements($form, $model, $params);

if (isset($elements)) {
    $form['elements'] = CMap::mergeArray($form['elements'], $elements);
}

$form = new CForm($form, $model);
echo '<div class="form">';
echo $form->render();
echo '</div>';

function parseCFormElements(&$form, $model, $params, $index = null)
{
    $form['inputElementClass'] = ext\fields\HFormInputElement::class;

    $params = CMap::mergeArray([
        'inline' => false, // переключение отображения формы: вертикальная/горизонтальная
    ], $params);
    $controller = $params['controller'];
    foreach ($model->fieldTypes as $fieldName => $fieldType) {
        $fieldParams = array();

        // форма на основе другой related (One-One или One-Many) модели
        if ($fieldType == 'form' || $fieldType == 'hasManyForm') {
            $isTabular = $fieldType == 'hasManyForm';
            $relModels = isset($params[$fieldName]) ? $params[$fieldName] : new $fieldName;
            $relModels = is_array($relModels) ? $relModels : array($relModels);
            if ($isTabular) {
                $form['elements'][] = '<div class="container-tabular" id="' . $fieldName . '-tabular">';
            }

            $relForms = array();
            foreach ($relModels as $i => $relModel) {
                $relForm = array();
                parseCFormElements(
                    $relForm,
                    $relModel,
                    CMap::mergeArray($params, ['inline' => true]),
                    $isTabular ? $i : null
                );
                $relForm = new CForm($relForm, $relModel);
                $relForm->renderBegin();
                $relFormHtml = $relForm->renderBody();
                $relForm->renderEnd();
                if ($isTabular) {
                    $relFormHtml = '<a href="" class="dnd_drager">' . CHtml::hiddenField('sortOrder[' . CHtml::modelName($relModel) . '][]', $i) . '</a>' . $relFormHtml . '<a href="" class="icon_delete"></a>';
                }

                array_push($relForms, $relFormHtml);
            }

            if ($isTabular) {
                $form['elements'][] = $controller->widget('zii.widgets.jui.CJuiSortable', array(
                    'items' => $relForms,
                    'itemTemplate' => '<li class="row row-tabular">{content}</li>',

                    // additional javascript options for the JUI Sortable plugin
                    'options' => array(
                        'placeholder' => 'ui-state-highlight',
                        'handle' => '.dnd_drager',
                        'start' => new CJavaScriptExpression('function(e, ui){
                            ui.placeholder.height(ui.item.height());
                        }'),
                    ),
                ), true);
                $form['elements'][] = '<a href="" class="btn" data-model-count="' . count($relModels) . '"><i class="icon_add"></i> Добавить</a></div>';
            } else {
                $form['elements'][] = implode('', $relForms);
            }

            continue;
        }

        if (is_array($fieldType)) {
            $fieldParams = $fieldType; // настройки поля, к примеру items для dropdownlist
            unset($fieldParams[0]);
            $fieldType = isset($fieldType['type']) ? $fieldType['type'] : $fieldType[0];
        }

        $fieldParams['type'] = $fieldType;

        $fieldHasJs = isset($fieldParams['js']) && is_a($fieldParams['js'], 'CJavaScriptExpression');
        if (is_a($fieldType, 'CJavaScriptExpression') || $fieldHasJs) {
            // поле с js или просто inline Js

            $js = $fieldHasJs ? $fieldParams['js'] : $fieldType;

            Yii::app()->clientScript->registerScript(uniqid(), $js);

            if ($fieldHasJs) {
                unset($fieldParams['js']);
            } else {
                continue;
            }

        }

        if ($fieldType == 'translit' || $fieldType == 'translitUrl') {
            $fieldParams = [
                'type' => '\application\widgets\translit\TranslitWidget',
                'model' => $model,
                'attribute' => $fieldName,
                'urlMode' => $fieldType == 'translitUrl',
            ];
        }

        if (in_array($fieldType, ['autocomplete', 'tags'])) {
            $additionaryOptions = [];

            if ($fieldType === 'tags') {
                $additionaryOptions = [
                    'search' => 'js:function() {
                        // custom minLength
                        var term = this.value.split( /,\s*/ ).pop();
                        if ( term.length < 2 ) {
                            return false;
                        }
                    }',
                    'focus' => 'js:function() {
                        // prevent value inserted on focus
                        return false;
                    }',
                    'select' => 'js:function( event, ui ) {
                        var terms = this.value.split( /,\s*/ );
                        // remove the current input
                        terms.pop();
                        // add the selected item
                        terms.push( ui.item.value );
                        // add placeholder to get the comma-and-space at the end
                        terms.push( "" );
                        this.value = terms.join( ", " );
                        return false;
                    }',
                ];
            }

            // Вторым параметром передаем (captureOutput) true.
            // таким образом мы запустим инициализацию скриптов, но текстовое поле писать не будем,
            // это сделает за нас CForm
            $fieldParams = [
                'type' => 'zii.widgets.jui.CJuiAutoComplete',
                'model' => $model,
                'attribute' => $fieldName,
                'sourceUrl' => $controller->createUrl('ac' . $fieldName),
                // additional javascript options for the autocomplete plugin
                'options' => CMap::mergeArray(
                    [
                        'minLength' => '2',
                    ],
                    $additionaryOptions
                ),
            ];
        }

        // Textarea более маленького размера и без использования виджета redactorJs
        if ($fieldType == 'textareaTiny') {
            $fieldParams['style'] = "width:400px;height:150px;";
            $fieldParams['type'] = 'textarea';
        }

        // textarea для редактирования markdown
        if ($fieldType == 'markdown') {
            $fieldParams = array(
                'type' => '\ext\markitup\HMarkitupWidget',
                //'htmlOptions'=>array('rows'=>15, 'cols'=>70),
                'options' => array(
                    //'previewParserPath'=>Yii::app()->urlManager->createUrl($preview)
                ),
            );
        }

        // datetime picker
        if ($fieldType == 'datetime') {
            $fieldParams = array(
                'type' => 'ext.jui.EJuiDateTimePicker',
                'model' => $model,
                'attribute' => $fieldName,
                'language' => 'ru', //default Yii::app()->language
                //'mode'    => 'datetime',//'datetime' or 'time' ('datetime' default)
                'options' => array(
                    'dateFormat' => 'yy-mm-dd',
                    // 'timeFormat' => 'hh:mm:ss',//'hh:mm tt' default
                    'addSliderAccess' => 'true',
                    'stepMinute' => 10,
                ),
            );
        }

        // autocomplete field for yandexMap
        if ($fieldType == 'yandexAutoComplete') {
            $fieldParams = array(
                'type' => 'application.widgets.yandex.YandexAutoComplete',
                'model' => $model,
                'attribute' => $fieldName,
                'latitudeAtt' => 'latitude',
                'longitudeAtt' => 'longitude',
                // additional javascript options for the autocomplete plugin
                'options' => array(
                    'minLength' => '2',
                ),
            );
        }

        if ($fieldType == 'file') {
            $fieldParams = array(
                'type' => 'ext.fields.HFileField',
            );
        }

        if ($fieldType == 'textarea') {
            $fieldParams = array(
                'type' => 'application.vendor.composer.vendor.yiiext.imperavi-redactor-widget.ImperaviRedactorWidget',
                'model' => $model,
                'attribute' => $fieldName,
                'options' => array(
                    'lang' => \Yii::app()->language,
                    'toolbarFixed' => true,
                    'toolbarFixedTopOffset' => 39,
                    'minHeight' => 300,
                    'focus' => false,
                    'imageUpload' => '/admin/imageupload',
                    'imageGetJson' => '/admin/uploadedimages',
                ),
            );
        }

        // html строка
        if (strpos($fieldType, "html:") === 0) {
            $form['elements'][] = substr($fieldType, 5);
            $form['elements'][] = '<p>';
            continue;
        }

        // добавляем элемент в форму
        if ($index !== null) {
            $fieldName = "[$index]" . $fieldName;
        }

        $form['elements'][$fieldName] = $fieldParams;

        if (!$params['inline']) {
            $form['elements'][] = '<p>';
        }

    }
}

ob_start();
?>
// вешаем обработчик на уровень выше, что бы он всегда срабатывал после валидации формы
$('#<?php echo $form->id ?>').parent().on('submit.ajaxSubmit', '#<?php echo $form->id ?>', function() {
    $.ajax({
        type: 'POST',
        dataType: 'JSON',
        url: $(this).prop('action'),
        beforeSend: startLoad,
        complete: stopLoad,
        data: $(this).serialize()+"&ajaxSubmit=1",
        context: $(this),
        error: function(xhr, textStatus, errorThrown){console.log(jQuery.parseJSON(xhr.responseText).content)},
        success: function (data) {parseAnswer(this, data)},
        cahe: false,
    });
    return false;
});

/**
 *  Обрабатывает ответ сервера
 **/
function parseAnswer($form, answer)
{
    if(!answer) {
        console.log('parseAnswer: No Data');
        return;
    }
    switch(answer.action)
        {
            case 404:
                 console.log(answer.content);
            break;
            case 'renewForm':
                $form.parent().replaceWith( answer.content );
                // Перезапускаем транспорт
                //prepareForm();
            break;
            case 'redirect':
                location.href = answer.content;
            break;
        }
}
<?php
$formJs = ob_get_clean();

Yii::app()->getClientScript()->registerScript('formJs', $formJs);
