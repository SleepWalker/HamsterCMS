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
    
    h1, h2 {
      font-size:16px;
      margin:5px auto;
    }
    
    h2 {
      font-size:13px;
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
      border: 2px solid #405000;
    }
    
    table.mainTable td,
    table.mainTable th {
      border: 1px solid #405000;
      text-align:center;
      color:#500000;
    }
    
    table.summTable td {
      border: none;
      text-align: right !important;
      font-weight: bold;
      font-size:15px;
      padding:10px;
    }
    
    table.mainTable th {
      padding: 5px 10px;
      background:#c3df92;
      color:#000;
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
      
    }
    
    p b {
      font-size:15px;
    }
    
    .font10 {
      font-size:10px;
    }
  </style>
  </head>
  <body>
    <div style="padding: auto 30px;">
      <p align="center"><img src="var:logo" /></p>
      <br />
      <p align="center">
        <u style="color:#500000;"><b>http://service.pwn-zone.com</b></u><br />
        <b>(044) 332-92-23</b>
      </p>
      <h1 align="center">Гарантийный талон №<?php echo $order->id ?></h1>
      
      
      <table class="mainTable">
        <?php
          printHeader();
          $checks = $order->check;
          $i = 1;
          foreach($checks as $check)
          {
            $prod = $check->prod;
            // если товаров больше 1 нам нужно для каждого из них распечатать отдельную строчку в гарантийном талоне
            for($j = 0; $j < $check->quantity; $j++) 
            {
              if($j == 21)
              {
                // создаем новую страницу и заново печатаем шапку
                echo '</table></div><pagebreak /><div style="padding: auto 30px;"><table class="mainTable">';
                printHeader();
              }
  ?>
<tr>
  <td><?php echo ($i++) ?></td>
  <td><?php echo $prod->code ?></td>
  <td class="align-left"><?php echo $prod->product_name ?></td>
  <td> </td>
  <td><?php echo $prod->waranty ?></td>
</tr>  
  <?php
            }
          }
        ?>
      </table>
      <?
        if($j > 7)
          // вставляем разрыв страницы и печатаем правила на другой стороне
          echo '<pagebreak />';
      ?>
      <p>
        Интернет магазин PWN-Zone.com благодарит вас за покупку. Этот гарантийный талон подтверждает наши гарантийные обязательства на данный товар. Пожалуйста, сохраняйте его до окончания срока гарантии.
      </p>
      <h2 align="center">Условия гарантийного обслуживания</h2>
      <div class="font10">
      <p>
        1. Гарантийное обслуживание возможно только при условии правильного и четкого заполнения данного гарантийного талона. Фактические модель и серийный номер изделия должны соответствовать указанным в гарантийном талоне.
      </p><p>
        2. Комплектация и внешний вид изделия проверяются клиентом при получении товара в присутствии представителя интернет магазина. Поставив подпись на данном документе, клиент подтверждает, что он не имеет претензий к комплектации и внешнему виду изделия.
        </p><p>
        3. Клиент обязан перед использованием изделия внимательно ознакомиться с инструкцией по эксплуатации, и в точности ее соблюдать.
        </p><p>
        4. Гарантийное обслуживание подразумевает бесплатное устранение неполадок изделия. Если ремонт невозможен, интернет магазин обязан произвести замену изделия на новое (аналогичное).
        </p><p>
        5. Негарантийными считаются следующие случаи: наличие механических повреждений, следов вскрытия или ремонтных работ, которые проводились третими лицами; использование изделия с нарушением правил его эксплуатации, использованием не по назначению; наличие внутри изделия посторонних предметов; повреждение целостности пломб и стикеров; отсутствие гарантийного талона или неправильно заполненный гарантийный талон (отсутствие даты, подписи клиента, печати, несовпадение серийных номеров).
        </p><p>
        6. Гарантийными случаями не считается также битые точки на LCD дисплеях или кластеры жестких дисков, в соответствии с общепринятыми стандартами (тоесть, в количестве не более 8шт.).
        </p><p>
        7. Транспортировка неисправного изделия осуществляется клиентом за его средства. В отдельных случаях возможен выезд специалиста интернет магазина на платной основе.
      </p>
      </div>
      <table width="60%">
        <tr>
          <td width="50%">Дата заказа:<td>
          <td width="50%"><?php echo Yii::app()->dateFormatter->format("dd MMMM y", $order->date); /*, HH:mm*/ ?></td>
        </tr>
        <tr>
          <td>Дата выдачи товара:<td>
          <td style="border-bottom: 1px solid #000;"></td>
        </tr>
      </table><br /><br />
      <table width="60%">
        <tr>
          <td width="50%">ФИО и подпись покупателя:</td>
          <td width="50%" style="border-bottom: 1px solid #000;"></td>
        </tr>
      </table>
    </div>
  </body>
</html>

<?php
function printHeader()
{
  ?>
  <tr>
    <th width="30">№</th>
    <th width="30">Код</th>
    <th>Наименование товара</th>
    <th width="250">Серийный номер</th>
    <th width="30">Гарантия</th>
  </tr>
  <?
}
?>
