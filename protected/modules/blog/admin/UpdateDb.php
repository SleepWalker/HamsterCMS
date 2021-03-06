<?php
namespace blog\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [1, 1.1, 1.2, '1.2.1'];
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

    /**
     * Добавленная колонка для рейтинга материалов
     */
    public function update1_2()
    {
        if (!isset(Yii::app()->db->getSchema()->getTable('blog')->columns['rating'])) {
            $this->startRawSql();
            ?>
ALTER TABLE  `blog` ADD  `rating` decimal(7,3) unsigned NOT NULL AFTER `tags`;
<?php
$this->endRawSql();
        }
    }

    /**
     * Поддержка вставки изображений через форму
     */
    public function update1_2_1()
    {
        if (!isset(Yii::app()->db->getSchema()->getTable('blog')->columns['attachmens'])) {
            $this->startRawSql();
            ?>
ALTER TABLE  `blog` ADD  `attachments` TEXT NOT NULL AFTER `status`;
<?php
$this->endRawSql();
        }
    }
}
