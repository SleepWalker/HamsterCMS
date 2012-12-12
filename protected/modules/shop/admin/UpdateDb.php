<?php

class UpdateDb extends HUpdateDb
{
  public function verHistory()
  {
    return array(1, 1.1);
  }
  
  public function update1_1()
  {
    $this->startRawSql();
?>
-- на всякий случай поменяем тип таблицы статус на тот, который нам действительно надо
ALTER TABLE `shop` CHANGE `status` `status` TINYINT( 3 ) UNSIGNED NOT NULL;
UPDATE `shop` SET `status`=6 WHERE `status`=1;
UPDATE `shop` SET `status`=1 WHERE `status`=2;
UPDATE `shop` SET `status`=2 WHERE `status`=4;
UPDATE `shop` SET `status`=4 WHERE `status`=5;
UPDATE `shop` SET `status`=5 WHERE `status`=6;
<?php
    $this->endRawSql();
  }
}
