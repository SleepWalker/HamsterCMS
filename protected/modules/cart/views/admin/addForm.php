<?php
$order = Order::model();
return array(
  'buttons'=>array(
    'submit'=>array(
      'type'=>'submit',
      'label'=>'Оформить',
      'attributes' => array(
        'class' => 'submit',
        'id' => 'submit',
      ),
    )
  ),
  'activeForm'=>array(
    'enableAjaxValidation'=>true,
    'enableClientValidation'=>false,
    'clientOptions' => array(
      'validateOnSubmit' => true,
      'validateOnChange' => true,
      // обработчики события afterValidate должны возвращать false, в том случае, если валидация прошла успешно
      'afterValidate' => 'js:function(form, data, hasError) {var afterValidateHasErrors = $("body").triggerHandler("afterValidate", [form, data, hasError]);return (!hasError && !afterValidateHasErrors)}',
    ),
  ),
  'elements' => array(
    'check' => array(
      'type' => 'form',
      'title' => 'Товары',
      'elements' => array(
        'prod_id'=>array(
          'type' => 'text',
          'attributes' => array(
            'name' => 'Shop[code]',
          ),
        ),
        '<div id="checkContainer"></div>',
      ),
    ),
    'client' => array(
      'type' => 'form',
      'title' => 'Данные клиента',
      'elements' => array(
        //'<a href="" class="icon_view"></a>',
        'first_name' => array(
          'type' => 'text',
        ),
        'last_name' => array(
          'type' => 'text',
        ),
        'email' => array(
          'type' => 'text',
        ),
      ),
    ),
    'address' => array(
      'type' => 'form',
      'title' => 'Адрес клиента',
      'elements' => array(
        'telephone' => array(
          'type' => 'text',
        ),
        'street' => array(
          'type' => 'text',
        ),
        'house' => array(
          'type' => 'text',
        ),
        'flat' => array(
          'type' => 'text',
        ),
      ),
    ),
    'order' => array(
      'type' => 'form',
      'title' => 'Данные заказа',
      'showErrorSummary'=>true,
      'elements' => array(
        'type' => array(
          'type' => 'dropdownlist',
          'items'=> $order->orderType,
          'prompt'=>'--Не выбрано--',
        ),
        'currency' => array(
          'type' => 'dropdownlist',
          'items'=> array(1=>$order->orderCurrency[1], 8 => $order->orderCurrency[8]),
        ),
        'status' => array(
          'type' => 'dropdownlist',
          'items'=> $order->orderStatus,
        ),
        'comment' => array(
          'type'=>'textarea',
        ),
      ),
    ),
  ),
);
?>
