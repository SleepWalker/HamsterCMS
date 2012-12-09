<?php

class updateDb 
{
  public $moduleId = 'shop';
  
  public $logStack = array();
  
  public function init()
  {
    Yii::app()->controller->module->setImport(array(
			'shop.models.*',
    ));
  }

  public function update($oldV, $newV)
  {
    $verHistory = array(1, 1.1);
    $c = Yii::app()->db;
    $tr = $c->beginTransaction();
    try
    {
      for($i = array_search($oldV, $verHistory) + 1; $i < count($verHistory); $i++)
      {
        switch($verHistory[$i])
        {
        case 1.1:
          ob_start();
?>
-- на всякий случай поменяем тип таблицы статус на тот, который нам действительно надо
ALTER TABLE `shop` CHANGE `status` `status` TINYINT( 3 ) UNSIGNED NOT NULL;
UPDATE `shop` SET `status`=6 WHERE `status`=1;
UPDATE `shop` SET `status`=1 WHERE `status`=2;
UPDATE `shop` SET `status`=2 WHERE `status`=4;
UPDATE `shop` SET `status`=4 WHERE `status`=5;
UPDATE `shop` SET `status`=5 WHERE `status`=6;
<?php
          $c->createCommand(ob_get_clean())->execute();
        }
        $this->logPush($oldV, $i);
        $oldV = $i;
      }
      $tr->commit();
      $this->log();
      return true;
    }
    catch(Exception $e) {
      $tr->rollback();
      Yii::log($e->getMessage(), 'error', 'hamster.update.db');
    }
    return false;
  }
  
  public function logPush($old, $new)
  {
    array_push($this->logStack, 'Успешное обновление базы данных модуля ' . $moduleId . ' (' . $old . '->' . $new . ')');
  }
  
  public function log()
  {
    foreach($this->logStack as $message)
    {
    Yii::log($message, 'info', 'hamster.update.db');
    }
  }
}
