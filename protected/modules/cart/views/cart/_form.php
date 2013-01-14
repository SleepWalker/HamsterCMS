<?php
$formAction = (isset($emoneyAction))?$emoneyAction:'/cart/order?step=' . ($step + 1);

$butt = function($value, $step = false, $submit = true) use ($formAction) {
  if($step)
    $formAction = preg_replace('/\d$/', $step, $formAction);
  return CHtml::ajaxButton($value, $formAction . '&ajax=1', array(
      'type' => 'POST',
      'success' => 'replaceContent',
      'beforeSend' => 'startLoad',
      'complete' => 'stopLoad',
      'error' =>'js:function(a){console.log(a.responseText)}',
  ),
  array(
      'type' => $submit ? 'submit' : 'button',
      'live' => false,
  ));
};

echo '<div id="cartContent" class="form">';
$form = $this->beginWidget('CActiveForm', array(
    'id'=>'cartForm',
    'enableAjaxValidation'=>true,
    'enableClientValidation'=>true,
    'action' => $formAction,
    'clientOptions' => array(
      'ajaxVar' => 'ajaxValidate',
    ),
)); 

if (is_object($model))
  echo $form->errorSummary($model);
if (is_object($model1))
  echo $form->errorSummary($model1);
if (is_object($model2))
  echo $form->errorSummary($model2);

switch($step)
{ 
  case 1:
    echo "<h1>Выберите способ оплаты</h1>"; 
    echo '<p>' . $form->radioButtonList($model, 'currency', $model->orderCurrency) . '</p>';
    if(in_array('Безналичный расчет', $model->orderCurrency) && isset(Yii::app()->params['currency']['toEmoney']))
      echo '<p><b>Внимание!</b> Б/Н оплата и оплата электронными деньгами произвдится по курсу: 1$=' . Yii::app()->params->currency['toEmoney'] . 'грн.<br />
    Пересчет суммы к оплате будет произведен перед последним шагом оформления заказа.
    </p>';
    
    echo "<h1>Выберите способ доставки</h1>"; 
    echo '<p>' . $form->radioButtonList($model, 'type', $model->orderType) . '</p>';
  break;
  case 2:
    echo "<h1>Введите информацию, необходимую для заказа</h1>"; 
    echo '<p>Поля, помеченные <span class="required">*</span> обязательны</p>';
    if(Yii::app()->user->isGuest)
    {
      echo '<div class="row" style="float:left;">' . $form->labelEx($model1, 'first_name') . $form->textField($model1, 'first_name', array('style'=>'width:180px;')) . $form->error($model1,'first_name') . '</div>';
      echo '<div class="row" style="float:right;">' . $form->labelEx($model1, 'last_name') . $form->textField($model1, 'last_name', array('style'=>'width:180px;')) . $form->error($model1,'last_name') . '</div>';
      echo '<div class="row"  style="clear:both;">' . $form->labelEx($model1, 'email') . $form->textField($model1, 'email', array('style'=>'width:565px')) . $form->error($model1,'email') . '</div>';
    }
    
    if(!Yii::app()->user->isGuest && count($model1->address))
    {
      echo '<fieldset>
        <legend>Ранее использованные вами контактные данные</legend>';
      
      // У залогиненого юзера могут быть адреса, потому даем ему возможность выбрать один из них
      $addressArr = array();
      foreach($model1->address as $address)
      {
        $addressArr[$address->id] = 'Тел: <b>' . $address->telephone . '</b>; ';
        if($address->street != '')
          $addressArr[$address->id] .= 'Адрес: <b>' . $address->street . ', ' . $address->house.(($address->flat)?', кв. '.$address->flat:'') . '</b>';
        if(!$first)        
          $first = $address->id;
      }
      
      $first = $oldAddress ? $oldAddress : $first;
      
      if(count($model1->address))
        $addressStyle = ' style="display:none;"';
      
      echo '<p>' . CHtml::radioButtonList('oldAddress', $first, $addressArr) . '</p>'; 
      
      echo '<br /><p>' . CHtml::checkBox('newAddress', false, array('onchange'=>'js:$("#address").toggle()')) . CHtml::label('Использовать другие контактные данные', 'newAddress') . '</p>';
        
      echo '</fieldset>';
    }

    echo '<fieldset id="address"' . $addressStyle . '>
    <legend>Контактные данные и адрес доставки</legend>';
    echo '<div class="row">' . $form->labelEx($model2, 'telephone') . $form->textField($model2, 'telephone', array('style'=>'width:545px')) . $form->error($model2,'telephone') . '</div>';
    if($model2->scenario == 'delivery') // Если выбрана доставка, то мы еще отображаем поля для ввода адреса
    {
      echo '<div class="row">' . $form->labelEx($model2, 'street') . $form->textField($model2, 'street', array('style'=>'width:545px')) . $form->error($model2,'street') . '</div>';
      echo '<div class="row">' . $form->labelEx($model2, 'house') . $form->textField($model2, 'house', array('style'=>'width:545px')) . $form->error($model2,'house') . '</div>';
      echo '<div class="row">' . $form->labelEx($model2, 'flat') . $form->textField($model2, 'flat', array('style'=>'width:545px')) . $form->error($model2,'flat') . '</div>';
    }  
    echo '</fieldset>';
    if ($model1->scenario == 'register')
    {
      echo '<fieldset>
      <legend>Пароли для доступа к сайту</legend>';
      echo '<div class="row">' . $form->labelEx($model1, 'password1') . $form->passwordField($model1, 'password1', array('style'=>'width:545px')) . $form->error($model1,'password1') . '</div>'
        . '<div class="row">' . $form->labelEx($model1, 'password2') . $form->passwordField($model1, 'password2', array('style'=>'width:545px')) . $form->error($model1,'password2') . '</div>'
        . '<p><b>Внимание</b>, на ваш <b>Email</b> придет письмо с ссылкой для активации аккаунта</p>';
      echo '</fieldset>';
    }
  break;
  case 3:
    ?>
      <h1><?php echo $user['first_name'] ?>, детали вашего заказа</h1>
      <br />
      <h2>Общая информация</h2>
      <p><b>Способ оплаты</b>: <?php echo $summary['currency'] ?></p>
      <p><b>Способ доставки</b>: <?php echo $summary['type'] ?></p>
      <p><b>Контактный номер</b>: <?php echo $address['telephone'] ?></p>
      <p><b>Адрес доставки</b>: <?php echo ($address['street'] ? 'ул. ' . $address['street'] . ', д. ' . $address['house'] . ( $address['flat'] ? ', кв. ' . $address['flat'] : '') : 'Не указан');?></p>
      <br />
      <h2>Товары в вашем чеке</h2>
    <?php

    $dataProvider=new CArrayDataProvider($summary['orderInfo'], array(
        'pagination'=>array(
            'pageSize'=>count($summary['orderInfo']),
        ),
    ));
    
    $this->widget('zii.widgets.grid.CGridView', array(
      'dataProvider'=>$dataProvider,
      'columns'=>array(
        array(
          'name' => '0',
          'header' => '',
          'type' => 'raw',
          'footer' => '<b>Итого:</b>',
        ),
        array(
          'name' => '1',
          'header' => 'Наименование товара',
          'type' => 'raw',
        ),
        array(
          'name' => '2',
          'header' => 'Количество',
          'htmlOptions' => array('align' => 'center'),
        ),
        array(
          'name' => '3',
          'header' => 'Цена за шт. <br /> Сумма',
          'footer' => '<b>' . $summary['orderPrice'] . ' грн.</b>',
          'htmlOptions' => array('width' => '100'),
          'type' => 'raw',
        ),
      ),
      'cssFile'=>false,
      'ajaxUpdate' => false,
      'pager'=>false,
        'summaryText'=>false,
    ));
  break;
  case 4:
    if($valid)
    {      
      if(isset($emoneyAction))
      {// Форма для перенаправления на оплату
        echo $emoneyFields;
        ?>
        <script type="text/javascript">
          // сразу отправляем форму на адрес мерчанта
          $("#cartForm").submit();
        </script>
        <noscript>
          <?php echo CHtml::submitButton('Перейти к оплате заказа'); ?>
        </noscript>
        <?php
      }
      else
      { 
        ?>
        <h1>Спасибо за покупку!</h1>
        <p>В ближайшее время с вами свяжется наш оператор для уточнения подробностей заказа.</p>
        
        <br />
        <p><a href="/" class="button">Вернутся на главную страницу</a></p>
        <?php
      }
    }
    else
    {
      ?>
      <h1>Извините, во время заказа произошла ошибка!</h1>
      <p>Для уточнения подробностей, пожалуйста свяжитесь с нами <a href="mailto:<?php echo Yii::app()->params['adminEmail']; ?>"><?php echo Yii::app()->params['adminEmail']; ?></a>.</p>
      <br />
      <p><a href="/" class="button">Вернутся на главную страницу</a></p>
      <?php
    }
  break;
}

echo '<p>'; 
if($step == 3)
    echo '<div style="float:right;">' . $butt( (isset($emoneyAction) ? 'Перейти к оплате заказа' : 'Завершить заказ') ) . '</div>';
if($step != 4)
  if($step > 1)
    echo $butt('Назад', $step - 1, false) . ' ';
  else
    echo '<a href="/cart" class="button">Назад</a> ';
  if($step == 3)
      echo CHtml::ajaxButton('Отмена заказа', $this->createUrl('/cart/clear'), array('complete'=>'js: function() {location.href="/"}'));
      
if($step == 1 && $askAboutAccount)
{//Справшиваем, есть ли у юзера аккаунт перед переходом на следующий шаг
  echo CHtml::link(CHtml::button('Далее'), '', array(
    'onclick' =>'runDialog("<h2 align=center>У вас уже есть аккаунт на нашем сайте?</h2>")', 
  ));
  
  echo '<p style="display:none;">' . $butt('Далее') . '</p>';
  
  $this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
    'selectors' => array(
      '#showLogin',
    ),
    'themeUrl' => '/css/jui',
    'options' => array(
      'title' => 'Корзина',
      'buttons' => 'js:{ 
        "Да": function() {
          $.ajax({
            "success":function(data){
              var id = runDialog(data);
              $("#"+id).dialog( "option", "buttons", false );
              $("#"+id).dialog( "option", "title", "Вход" );
              $( "#"+id ).bind( "dialogbeforeclose", function(event, ui) {
                $("#yt1").click();
              });
              $(this).dialog("close");
            },
            "url":"/site/login/?ajax=1",
            "breforeSend":startLoad,
            "context": $(this),
            "complete":stopLoad,
            "cache":false,
            "error":function(ans) {
              console.log(ans.responseText);
            },
          });        
        },
        "Нет, создать новый": function() { 
          $("<input type=\"hidden\" name=\"newUser\" value=\"1\">").appendTo($("#cartForm"));
          $("#yt1").click();
          $(this).dialog("close"); 
        },
        "Продолжить без регистрации": function() { 
          $("#yt1").click();
          $(this).dialog("close"); 
        },
      }',
    ),
  ));
}
else
{    
  if($step < 3)
  {
    echo $butt('Далее');
  }
}
echo '</p>';
echo '</div>'; //#cartContent
  
//echo CHtml::hiddenField('step', $step);

$this->endWidget(); 
?>
