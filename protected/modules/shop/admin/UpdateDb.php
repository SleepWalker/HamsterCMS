<?php

class UpdateDb extends HUpdateDb
{
  public function verHistory()
  {
    return array(1, 1.1, 1.2, '1.2.1');
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

  /**
   * В этом обновлении мы проследим, что бы у колонки id была длина 10 знаков, 
   * так как теперь длина кода контролируется в модели
   */
  public function update1_2()
  {
    if(Shop::model()->tableSchema->columns['id']->size != 10)
    {
      $this->startRawSql();
?>
ALTER TABLE  `shop` CHANGE  `id`  `id` INT( 10 ) UNSIGNED NOT NULL;
<?
      $this->endRawSql();
    }
  }
  public function update1_2_1()
  {
    if(empty(Shop::model()->tableSchema->columns['code']))
    {
      $this->startRawSql();
?>
ALTER TABLE  `shop` CHANGE  `id`  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE  `shop` ADD  `code` INT UNSIGNED NOT NULL AFTER  `id`;
UPDATE `shop` SET `code`=`id`;
<?
      $this->endRawSql();
    }
  }
}
