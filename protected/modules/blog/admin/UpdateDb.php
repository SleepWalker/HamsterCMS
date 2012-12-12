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
    $this->endRawSql();

    $this->c->createCommand('ALTER TABLE `blog` ADD `cat_id` MEDIUMINT(4) UNSIGNED NOT NULL AFTER `user_id` , ADD INDEX ( `cat_id` )')->execute();
    $this->c->createCommand('UPDATE `blog` SET `cat_id`=1')->execute();

    $this->c->createCommand()->addForeignKey('FK_blog_categorie_id', Post::model()->tableName(), 'cat_id', Categorie::model()->tableName(), 'id', 'CASCADE', 'CASCADE');
  }
}
