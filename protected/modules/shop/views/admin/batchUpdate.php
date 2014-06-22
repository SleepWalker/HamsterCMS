<?
/**
 * View file for batch update of models
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.views.batchUpdate
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
?>
<div class="form batchUpdate">
<p><b>Управление таблицей</b> (работает когда курсор в одном из текстовых полей):</p>
<ul>
  <li>enter - добавить характеристику</li>
  <li>ctr+enter - сохранить</li>
  <li>ctr+up arrow - сдвинуть строку выше</li>
  <li>ctr+down arrow - сдвинуть строку ниже</li>
</ul>
<?php 
echo $header;
echo CHtml::beginForm(); ?>
<table>
<tr>
<th></th><th>Название характеристики</th><th>Суффикс/Варианты выбора</th><th title="Обязательное поле">*</th><th title="Режим заголовка">З</th><th>Тип</th></tr>
<tr><td colspan="7" align="center">
<?php 
  echo CHtml::button('Добавить поле характеристики', array(
    'onclick'=>'
      var el = $(this).parents("tr").nextAll("tr").filter(":last").clone();
      $("input[type=text]", el).prop("value", "");
      $("input", el).removeProp("checked", "");
      $("select option", el).prop("selected", "");
      $("td", el).removeClass("status_3");
      // Меняем индекс массива в атрибуте name
      // Отнимаем заголовок и строку с "Добавить поле..." - 1, что бы получить номер последней строки
      var curInd = $(this).parents("table").find("tr").length - 3;
      $("input, select", el).prop("name", function (index, value) {
        return value.replace("["+ curInd +"]", "["+ (curInd+1) +"]");
      });
      el.appendTo($(this).parents("table"));
      el.find("input[type=text]").eq(0).focus();
    ',
  ));
?>
</td></tr>
<?php foreach($model as $i => $curModel) { ?>
  <tr>
  <td> <?php echo CHtml::activeHiddenField($curModel, "[" . $i . "]sindex") ?>
  <a href="" class="sortUp icon_sort_asc"></a>
  <a href="" class="sortDown  icon_sort_desc"></a>
  </td>
  <?php 
  //foreach($attributes as $attributeName) { 
  echo '<td>';
  echo CHtml::activeTextField($curModel, "[" . $i . "]char_name");
  echo '</td>';
  
  echo '<td>';
  echo CHtml::activeTextField($curModel, "[" . $i . "]char_suff"); 
  // Иконка редактирования вариантов выбора для характеристики
  // Открывает новый dialog
  echo CHtml::link('', '#', array('class'=>'icon_table runCharVarDialog', 'style'=>'display:inline-block;')); 
  echo '</td>';
  
  echo '<td>' .
    // чекбокс, включающий обязательность поля
    CHtml::activeCheckBox($curModel, "[" . $i . "]isRequired", array('title'=>'Это поле должно быть обязательным'))
    . '</td>';
    
  echo '<td>' .
    // чекбокс, включающий режим отображения "Заголовок"
    CHtml::activeCheckBox($curModel, "[" . $i . "]isCaption", array('title'=>'Включить режим заголовка'))
  . '</td>';
  
  echo '<td>' . 
    CHtml::activeDropDownList($curModel, "[" . $i . "]type", $curModel->typeList)
  . '</td>';

  echo '<td>' .
    CHtml::link('', '#', array('class'=>'icon_delete', 'onclick'=>'
      $(this).parents("tr").remove();
      return false;
    ')) 
    . '</td></tr>';
  // Добавляем поле с id модели и с индексом сортировки
  // (все поля размещаются после таблицы, что бы они не клонировались при добавлении новых строк в таблицу)
  if(!empty($curModel->char_id))
    $pkInputs .= CHtml::activeHiddenField($curModel, "[" . $i . "]char_id");
} ?>
</table>
<?php 
echo '<p>' . CHtml::errorSummary($model) . '</p><p>'.
CHtml::ajaxSubmitButton('Сохранить', $this->createUrl('', 'id' => $this->crudid), array(
  'data'=>'js:jQuery(this).parents("form").serialize()+"&ajaxSubmit=1"',
  'beforeSend' => 'startLoad',
  'complete' => 'stopLoad',
  'error' => 'function(xhr, textStatus, errorThrown){alert(xhr.responseText)}',
  'success' => 'function (answer) {/*alert(answer);*/
    answer = jQuery.parseJSON(answer);
    switch(answer.action)
    {
      case "renewForm":
        location.reload();
        //$( $("#charShemaSubmit")[0].form.parentNode ).replaceWith( answer.content );
      break;
    }
  }',
), array('id'=>'charShemaSubmit', 'live'=>false)) // live Для отключения jQuery live event
. '</p>' . $pkInputs;
?>
<?php echo CHtml::endForm(); ?>
</div>

<?php
$this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
  'id'=>'dnd',
  'selectors' => array(
    '#dnd a.icon_table',
  ),
  'themeUrl' => $this->adminAssetsUrl . '/css/jui',
));
// Создает окно с редактированием вариантов выбора для характеристики
$js = '
$("body").off("click.sortDown");
$("body").on("click.sortDown", "table .sortDown", function() {
  $(this).parents("tr").insertAfter($(this).parents("tr").next());
  var sindexInput1 = $(this).parents("td").addClass("status_3")
  .find("input");
  var sindexInput2 = $(this).parents("tr").prev().find("td").eq(0).find("input");
  // меняем местами значения индекса сортировки из инпутов
  var val1 = sindexInput1.val();
  sindexInput1.val(sindexInput2.val());
  sindexInput2.val(val1);
  return false;
});
$("body").off("click.sortUp");
$("body").on("click.sortUp", "table .sortUp", function() {
  // блокируем тасование с первыми двумя строчками таблицы (шапка и кнопка "добавить")
  if($(this).parents("tr").prev()[0] === $(this).parents("table").find("tr")[0]
  || $(this).parents("tr").prev()[0] === $(this).parents("table").find("tr")[1])
    return false;
  $(this).parents("tr").insertBefore($(this).parents("tr").prev());
  var sindexInput1 = $(this).parents("td").addClass("status_3")
  .find("input");
  var sindexInput2 = $(this).parents("tr").next().find("td").eq(0).find("input");
  // меняем местами значения индекса сортировки из инпутов
  var val1 = sindexInput1.val();
  sindexInput1.val(sindexInput2.val());
  sindexInput2.val(val1);
  return false;
});
// вешает обработчик события на все Input внутри контейнера с классом @className
// обработчик события обеспевает добавление нового поля по нажатию enter и отправку формы по нажатию ctrl+enter
// а так же ctrl+shift+кнопка вверх(или вниз) для сортировки
function setEnterCtrlEnterEvents(className, enter, ctrlenter)
{
  $("." + className + " input").keydown(function(event) {
    if (event.keyCode == 13 && event.ctrlKey)
      return ctrlenter(this);
    if (event.keyCode == 13 && !event.ctrlKey)
    {  
      return enter(this);
    }
    if (event.keyCode == 38 && event.ctrlKey) // кнопка вверх
    {
      $(this).parents("tr").find(".sortUp").click();
      $(this).focus();
      return false;
    }
    if (event.keyCode == 40 && event.ctrlKey) // кнопка вниз
    {
      $(this).parents("tr").find(".sortDown").click();
      $(this).focus();
      return false;
    }
  });
}

setEnterCtrlEnterEvents("batchUpdate", function(obj) {
  // Добавляем еще одно поле характеристики
  var el = $(obj).parents("table").find("tr").filter(":last").clone(true);
  $("input[type=text]", el).prop("value", "");
  $("input", el).removeProp("checked", "");
  $("select option", el).prop("selected", "");
  $("td", el).removeClass("status_3");
  // Меняем индекс массива в атрибуте name
  // Отнимаем заголовок и строку с "Добавить поле..." - 1, что бы получить номер последней строки
  var curInd = $(obj).parents("table").find("tr").length - 3;
  $("input, select", el).prop("name", function (index, value) {
    return value.replace("["+ curInd +"]", "["+ (curInd+1) +"]");
  });
  el.appendTo($(obj).parents("table"));
  el.find("input[type=text]").eq(0).focus();
  return false;
}, function(obj) {
  $(obj).parents("form").find("input[type=submit]").click();
});

$("body").off("click.runCharVarDialog");
$("body").on("click.runCharVarDialog", ".runCharVarDialog", function() {
  runCharVarDialog(this);
  return false;
});
// Закрывает диалог и заполняет данными поле, для которого его вызвали
window.destroyCharVarDialog = function(obj, separator, dialogId)
{
  if(!separator) separator = ";";
  var inputs = new Array();
  $("tr", $(obj).parents("div").find("table")[0]).each(function() {
    if(separator != "::")
      $("input[type=text]", $(this)).each(function(index) {
        var value = $(this).prop("value");
        if (value == "") return true; // пропускаем итерацию

        inputs.push(value);
      });
    else
    {
      // обработка формы, где выбираются зависимые характеристики
      $("input[type=checkbox]:checked", $(this)).each(function() {
        inputs.push($(this).val());
      });
    }
  });
  var value=inputs.join(separator);
  // Если в value присутствует оператор, причем отличный от первоновального ";"
  // Другими словами это характеристика зависящая от другой характеристики
  // заворачиваем значение в {{}}
  if(separator == "::" && value != "")
    value = "{{" + value + "}}";
  var inputObj = "inputObj" + dialogId;
  $($(obj).parents("div")[0]).dialog("destroy");
  $("body").data(inputObj).prop("value", value)
  .focus();
}

window.runCharVarDialog = function(obj, separator, parentInputObj)
{
  // obj обьект, на котором был клик
  if(!separator) separator = ";";
  // сохраняем id диалога в переменную (будем использовать его для доступа к переменным в $().data)
  var dialogId = $(obj).parents("div")[0].id;
  var inputObj = "inputObj" + dialogId;
  $("body").data(inputObj, $(obj).prev("input"));
  var delBut = ' . CJavaScript::encode( CHtml::link('', '#', array('class'=>'icon_delete', 'onclick'=>'$(this).parents("tr").remove();return false;')) ) . ';

  var values = $("body").data(inputObj).prop("value");
  if(separator != ";")
    values = values.slice(2,-2); // убираем {{ и }} по краям строки

  values = values.split(separator);
  if(typeof(values) == "string") // если values - строка, создадим массив с одним элементом
    values = [values];
    
  var strInputs = "";
  var newSeparator = "::";
  var tableHeader = (separator != "::") ? "<tr><th></th><th>Вариант выбора</th><th>Зависимые характеристики</th></tr>"
                    : "<th>Название характеристики</th><th>Суффикс/Варианты выбора</th><th>Тип</th>";
  if(separator == ";")
  {
    for(var i in values)
    {
      j = i*1+1;
      if (values[j] && values[j].indexOf("{{") != -1)
      {
        // если следующий элемент содержит {{, это характеристики зависимые от текущей характеристики
        // выделяем эти характеристики
        //var sliceStart = values[j].indexOf("{{");
        //subValues = values[j].slice(sliceStart + 2, -2);
        subValues = values[j];
        // удаляем значение след. елемента из массива
        // таким образом мы перепрыгним элемент с подхарактеристиками и цикл далее пойдет по характеристикам верхнего уровня
        delete values[j];
      }
      else subValues = "";
  
      if(separator == ";")
      {
        strInputs += "<tr>";
        // сортировка
        strInputs += "<td><a href=\"\" class=\"sortUp icon_sort_asc\"></a><a href=\"\" class=\"sortDown  icon_sort_desc\"></a></td>";
        // вариант выбора
        strInputs += "<td><input type=\"text\" value=\"" + values[i] + "\"></td>";
        // зависимые характеристики
        strInputs += "<td><input type=\"text\" value=\"" + subValues + "\">"; 
        strInputs += "<a href=\"#\" class=\"icon_table\" onclick=\"runCharVarDialog(this, \'" + newSeparator + "\', \'" + inputObj + "\'); return false;\"></a>";
        strInputs += "<td>" + delBut + "</td></tr>";
      }
    }
  }else{
    tableHeader = "<tr><th>Выбрать зависимые характеристики</th></tr>";
    strInputs += "<tr><td>";
    // Создаем список с чекбоксами, что бы юзер мог выбрать какие характеристики должны зависеть от текущей
    // (при условии, что у характеристики тип отличный от текстового поля)
    
    // Достаем из главного диалога все названия характеристик
    // Генерируем из них список чекбоксов
    var parentDialogObj = $("body").data(parentInputObj).parents("div");
    parentDialogObj.find("tr td:nth-child(2) input[type=text]").each(function()
    {
      var charId = $(this).prop("id").replace("char_name", "char_id");
      charId = $("#"+charId).val(); // id характеристики верхнего уровня
      var randomId = new Date().getTime(); // строка для обеспечения уникальности id
      var checked = ($.inArray(charId, values) != -1)?\'checked="checked"\':"";
      strInputs += \'<p><input type="checkbox" name="relatedChar" value="\' + charId + \'" id="\' + $(this).prop("id") + randomId + \'" \' + checked + \' /> <label for="\' + $(this).prop("id") + randomId + \'">\' + $(this).val() + \'</label></p>\';
    });
    strInputs += "</td></tr>";
  }
  var dialogStr = \'<table class="batchUpdateDialog">\' + tableHeader;
  if(separator == ";")
    dialogStr += \'<tr><td colspan="3" align="center">' . 
    CJavaScript::quote(
      CHtml::button('Добавить вариант выбора', array(
        'onclick'=>'
          var el = $(this).parents("tr").next().clone();
          $("input, select", el).prop("value", "");
          $("select option", el).prop("selected", "");
          el.appendTo($(this).parents("table")[0]);
          el.find("input").eq(0).focus();
        ',
      ))
    )
    . '</td></tr>\';
    dialogStr += strInputs + \'</table><p>' .
    '<input type="submit" value="Сохранить" onclick="destroyCharVarDialog(this, &quot;\' + separator + \'&quot;, &quot;\' + dialogId + \'&quot;); return false;" />'
    . '</p>\';
  runDialog(dialogStr);
  // выделяем первый input
  $(".batchUpdateDialog").find("input[type=text]").eq(0).focus();
  // включаем добавление новых строк по enter и закрытие диалога по ctrl+enter
  if(separator == ";")
    setEnterCtrlEnterEvents("batchUpdateDialog", function(obj) {
      // Добавляем еще одно поле характеристики
      var el = $(obj).parents("table").find("tr").filter(":last").clone(true);
      $("input", el).prop("value", "");
      $("td", el).removeClass("status_3");
      el.appendTo($(obj).parents("table"));
      el.find("input[type=text]").eq(0).focus();
      return false;
    }, function(obj) {
      $(obj).parents("div").find("input[type=submit]").click();
    });
}';

Yii::app()->getClientScript()->registerScript('runCharVarDialog', $js);
?>
