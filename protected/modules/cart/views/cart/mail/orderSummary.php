<table align="center" cellpadding="5" cellspacing="0" width="70%" style="border:1px #1D1D1D solid; font-size:14px;font-family: verdana;border-collapse: collapse;">
<tr>
<td style="background:#1D1D1D; color:#fff;" align="center" colspan="2">
Интернет магазин для геймеров
</td>
</tr>
<tr>
<td style="background:#BCDC61; color:#1D1D1D; font-weight:bold; font-size:40px;font-family: Georgia;" align="center" colspan="2">
PWN-Zone.com
</td>
</tr>

<tr style="background:#fcfcfc;">
<td valign="top" align="left" style="width:60%;">
<p style="font-size:20px;">Здравствуйте, <b><?php echo $user['first_name']  ?></b></p>

<p>Благодарим, что воспользовались услугами интернет магазина PWN-Zone.com!<br />
Ваш заказ <b>№<?php echo $summary['orderNo']  ?> от <?php echo $summary['orderDate']  ?></b> получен нашим оператором.<br />
В скором времени мы с Вами свяжемся</p>
</td>

<td align="left">
<p align="center"><b>Подробности заказа</b></p>
  <table cellpadding="5" border="0">
    <tr>
      <td>Заказчик:</td>
      <td><?php echo $user['first_name'] . ' ' . $user['last_name']  ?></td>
    </tr>
    <tr>
      <td>E-mail:</td>
      <td><?php echo $user['email']  ?></td>
    </tr>
    <tr>
      <td>Телефон:</td>
      <td><?php echo $address['telephone'] ?></td>
    </tr>
    <tr>
      <td>Доставка:</td>
      <td><?php echo $summary['type'] ?></td>
    </tr>
    <tr>
      <td>Адрес доставки:</td>
      <td><?php echo 'ул. ' . $address['street'] . ', д. ' . $address['house'] . ( $address['flat'] ? ', кв. ' . $address['flat'] : '');?></td>
    </tr>
  </table>
</td>
</tr>
<tr style="background:#1D1D1D; color:#fff;">
<td valign="top" align="left">
<p style="font-size:20px;"><b>Вы заказали:</b></p>
<ul>
<?php
  foreach($cart as $prod)
  {
    echo '<li>' . $prod->product_name . ' (' . $prod->id . ')' . ' — ' . $prod->price . ' грн.';
    if(isset($prod->variants))
      foreach($prod->variants as $name => $value)
        echo "<br />$name: $value";
    echo '</li>';
  }
?>
</ul>
</td>
<td valign="top" align="left">
  <table style="background:#1D1D1D; color:#fff;" cellpadding="5" border="0">
    <tr>
      <td>Cпособ оплаты:</td>
      <td><?php echo $summary['currency']  ?></td>
    </tr>
    <tr>
      <?php
        if($summary['delivery'])
        {
          echo '<tr><td>Стоимость товаров:</td><td>' . $summary['orderPrice'] . '</td></tr>';
          $summary['orderPrice'] = number_format($summary['amount'] + 30, 2, ',', ' ');
          echo '<tr><td>Стоимость доставки:</td> <td>30 грн.</td></tr>';
        }
      ?>
    </tr>
    <tr style="color:#BCDC61;font-weight:bold;font-size:20px;">
      <td>Всего к оплате:</td><td><?php echo $summary['orderPrice'] ?> грн.</td>
    </tr>
  </table>
</td>
</tr>
</table>
<table align="center" cellpadding="0" cellspacing="0" width="70%" style="font-size:12px;font-family: verdana;">
<tr><td align="left">
<p>C уважением, администрация интернет магазина <a href="http://shop.pwn-zone.com">PWN-Zone.com</a><br />
Это письмо сгенерировано автоматически, отвечать на него не нужно</p>
</td>
<td align="right">
<img alt="Интернет магазин - PWN-Zone.com" style="float:right; height:60px;" src="data:image/jpeg;base64,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">
</td>
</tr></table>
