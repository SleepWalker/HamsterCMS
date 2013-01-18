<?php
global $N0, $Ne0, $Ne1, $Ne2, $Ne3, $Ne6;

$N0 = 'ноль';

$Ne0 = array(
              0 => array('','один','два','три','четыре','пять','шесть',
                         'семь','восемь','девять','десять','одиннадцать',
                         'двенадцать','тринадцать','четырнадцать','пятнадцать',
                         'шестнадцать','семнадцать','восемнадцать','девятнадцать'),
              1 => array('','одна','две','три','четыре','пять','шесть',
                         'семь','восемь','девять','десять','одиннадцать',
                         'двенадцать','тринадцать','четырнадцать','пятнадцать',
                         'шестнадцать','семнадцать','восемнадцать','девятнадцать')
              );

$Ne1 = array('','десять','двадцать','тридцать','сорок','пятьдесят',
              'шестьдесят','семьдесят','восемьдесят','девяносто');

$Ne2 = array('','сто','двести','триста','четыреста','пятьсот',
              'шестьсот','семьсот','восемьсот','девятьсот');

$Ne3 = array(1 => 'тысяча', 2 => 'тысячи', 5 => 'тысяч');

$Ne6 = array(1 => 'миллион', 2 => 'миллиона', 5 => 'миллионов');

function written_number($i, $female=false) {
   global $N0;
   if ( ($i<0) || ($i>=1e9) || !is_int($i) ) {
     return false; // Аргумент должен быть неотрицательным целым числом, не превышающим 1 миллион
   }
   if($i==0) {
     return $N0;
   }
   else {
     return preg_replace( array('/s+/','/\s$/'),
                          array(' ',''),
                          num1e9($i, $female));
     return num1e9($i, $female);
   }
}

function num_125($n) {
   /* форма склонения слова, существительное с числительным склоняется
    одним из трех способов: 1 миллион, 2 миллиона, 5 миллионов */
   $n100 = $n % 100;
   $n10 = $n % 10;
   if( ($n100 > 10) && ($n100 < 20) ) {
     return 5;
   }
   elseif( $n10 == 1) {
     return 1;
   }
   elseif( ($n10 >= 2) && ($n10 <= 4) ) {
     return 2;
   }
   else {
     return 5;
   }
}

function num1e9($i, $female) {
   global $Ne6;
   if($i<1e6) {
     return num1e6($i, $female);
   }
   else {
     return num1000(intval($i/1e6), false) . ' ' .
       $Ne6[num_125(intval($i/1e6))] . ' ' . num1e6($i%1e6, $female);
   }
}

function num1e6($i, $female) {
   global $Ne3;
   if($i<1000) {
     return num1000($i, $female);
   }
   else {
     return num1000(intval($i/1000), true) . ' ' .
       $Ne3[num_125(intval($i/1000))] . ' ' . num1000($i%1000, $female);
   }
}

function num1000($i, $female) {
   global $Ne2;
   if( $i<100) {
     return num100($i, $female);
   }
   else {
     return $Ne2[intval($i/100)] . (($i%100)?(' '. num100($i%100, $female)):'');
   }
}

function num100($i, $female) { 
   global $Ne0, $Ne1;
   $gender = $female?1:0;
   if ($i<20) {
     return $Ne0[$gender][$i];
   }
   else {
     return $Ne1[intval($i/10)] . (($i%10)?(' ' . $Ne0[$gender][$i%10]):'');
   }
}

// функция ucfirst для кодировки utf-8
function ucfirst_utf8($str)
{
  return mb_substr(mb_strtoupper($str, 'utf-8'), 0, 1, 'utf-8') . mb_substr($str, 1, mb_strlen($str)-1, 'utf-8');
}
 

/*
Рассмотрим примеры использования функции written_number():
$ruble = array(1 => 'рубль', 2 => 'рубля', 5 => 'рублей');
$sum = 21802;
echo 'Всего оказано услуг на сумму: '
     .  written_number($sum) . ' ' . $ruble[num_125($sum)] . ' 00 коп.';
$friendm = array(1 => 'друг', 2 => 'друга', 5 => 'друзей');
$friendf = array(1 => 'подруга', 2 => 'подруги', 5 => 'подруг');
$m_count = 11;
$f_count = 21;
echo 'У пользователя ' . written_number($m_count) . ' ' . $friendm[num_125($m_count)]
     . ' и ' . written_number($f_count, true) . ' ' . $friendf[num_12
     */
?>

<!DOCTYPE HTML>
<html>
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
    body, html {
      padding:0;
      margin:0;
      list-style:none;
    }
    
    body {
      font-size: 13px;
    }
    
    h1 {
      font-size:16px;
      margin:5px auto;
    }
    
    hr {
      margin: 5px 0 20px 0;
    }
    
    table {
      width: 100%;
      margin: 10px 0;
      border-collapse: collapse;
    }
    
    table.mainTable {
      border: 2px solid #000;
    }
    
    table.mainTable td,
    table.mainTable th {
      border: 1px solid #000;
      text-align:center;
    }
    
    table.summTable td {
      border: none;
      text-align: right !important;
      font-weight: bold;
      font-size:15px;
      padding:10px;
    }
    
    th {
      padding: 5px 10px;
      background:#e9e9e9;
      text-align:center;
    }
    
    td {
      padding: 2px;
    }
    
    .valign-top td {
      vertical-align:top;
    }
    
    table.mainTable td.align-left {
      text-align:left !important;
    }
    
    td,
    th {
      border:1px #000 solid;
    }
    
    li {
      font-size: 14px;
    }
    
    img {
      width:230px;
      height: 62px;
    }
    
    p b {
      font-size:15px;
    }
  </style>
  </head>
  <body>
    <h1>Подтверждение оплаты заказа №<?php echo $order->id ?> от <?php echo Yii::app()->dateFormatter->format("dd MMMM y", $order->date); ?></h1>
    <hr style="height:2px;border:0;color:#000;background:#000;" />
    
    <table>
      <tr>
        <td>
          <table class="valign-top">
            <tr>
              <td><u>Продавец:</u></td>
              <td><b>интернет магазин PWN-Zone г. Киев <br />тел. (044) 332-92-23, http://shop.pwn-zone.com</b></td>
            </tr>
            <tr>
              <td><u>Покупатель:</u></td>
              <td><b><?php
                $user = $order->user;
                if(!$user->id) // заказ без регистрации -> тянем юзера из другой модели
                  $user = $order->client;
                echo $user->last_name . ' ' . $user->first_name;
              ?></b></td>
            </tr>
          </table>
        </td>
        <td><img alt="logo" src="var:logo" /></td>
      </tr>
    </table>
    
    <table class="mainTable">
      <tr>
        <th width="30">№</th>
        <th width="30">Код</th>
        <th>Наименование товара</th>
        <th width="30">Кол-во</th>
        <th width="70">Цена</th>
        <th width="70">Сумма</th>
      </tr>
      <?php
        $checks = $order->check;
        foreach($checks as $i => $check)
        {
          $prod = $check->prod;
          $total += $prod->price * $check->quantity;
        ?>
      <tr>
        <td><?php echo ($i+1) ?></td>
        <td><?php echo $prod->code ?></td>
        <td class="align-left"><?php echo $prod->product_name ?></td>
        <td><?php echo $check->quantity ?></td>
        <td><?php echo number_format($prod->price, 2, ",", " ") ?></td>
        <td><?php echo number_format($prod->price * $check->quantity, 2, ",", " ") ?></td>
      </tr>  
        <?php
        }
      ?>
    </table>
    <table class="summTable">
      <tr>
        <td>Итого:</td>
        <td><?php echo number_format($total, 2, ",", " ") ?></td>
      </tr>
    </table>
    <p>
      Всего наименований <?php echo ($i+1) ?>, на сумму <?php echo number_format($total, 2, ",", " ") ?> грн. <br />
      <b><?php
        $uah = array(1 => 'гривня', 2 => 'гривни', 5 => 'гривень');
        echo ucfirst_utf8(written_number((int)$total, true)) . ' ' . $uah[num_125($total)] . ' ' . str_pad(($total - floor($total)), 2, "0", STR_PAD_LEFT) . ' копеек';
      ?></b>
    </p>
    <p>
      Настоящим подтверждаю, что мною проверены комплектация и внешний вид приобретенных изделий, а также правильность заполнения гарантийного листа.
    </p>
    <table>
      <tr>
        <td width="180">ФИО и подпись покупателя:</td>
        <td style="border-bottom: 1px solid #000;"></td>
      </tr>
    </table>
  </body>
</html>
