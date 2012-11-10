<?php

class updateDb 
{
  public function init()
  {
    Yii::app()->controller->module->setImport(array(
			'blog.models.*',
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
CREATE TABLE IF NOT EXISTS `blog_categorie` (
  `id` mediumint(4) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `parent` mediumint(4) unsigned NOT NULL,
  `sindex` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_alias` (`alias`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
INSERT IGNORE INTO `blog_categorie` (`alias`, `name`, `parent`, `sindex`) VALUES ('pervaya_kategoriya', 'Первая категория', 0, 0);
<?php
          $c->createCommand(ob_get_clean())->execute();

          $c->createCommand('ALTER TABLE `blog` ADD `cat_id` MEDIUMINT(4) UNSIGNED NOT NULL AFTER `user_id` , ADD INDEX ( `cat_id` )')->execute();
          $c->createCommand('UPDATE `blog` SET `cat_id`=1')->execute();

          $c->createCommand()->addForeignKey('FK_blog_categorie_id', Post::model()->tableName(), 'cat_id', Categorie::model()->tableName(), 'id', 'CASCADE', 'CASCADE');
        }
      }
      $tr->commit();
      return true;
    }
    catch(Exception $e) {
      $tr->rollback();
      Yii::log($e->getMessage(), 'error', 'hamster.update.db');
    }
    return false;
  }
}
