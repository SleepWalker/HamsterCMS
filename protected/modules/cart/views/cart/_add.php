<?php
  foreach($cart as $prod)
  {
    $qntty += $prod->quantity;
    $summ += $prod->price;
  }
  
  $this->renderPartial('cart', array(
    'models' => $cart,
    'hidePayButton' => true,
  ), false, true);
?>
<p>
  <a href="/cart" class="button" onclick="$(this).parents('.ui-dialog-content').find('form').submit(); return false;">Перейти к оформлению заказа</a>
  <a href="" class="button" onclick="$(this).parents('.ui-dialog-content').prev().find('.ui-icon-closethick').click(); return false;">Продолжить покупки</a>
</p>
<script type="text/javascript">
<?php
echo 'renderCartStatus("' . $qntty . '", "' . $summ . '");';
?>
</script>