<?php
namespace sociality\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [1, 1.1, '1.1.1'];
    }

    /**
     * Добавляем колонку для ip юзера
     */
    public function update1_1()
    {
        if (empty(Comment::model()->tableSchema->columns['ip'])) {
            $this->startRawSql();
            ?>
ALTER TABLE  `comment` ADD `ip` INT UNSIGNED NOT NULL
<?
      $this->endRawSql();
    }
  }

  /**
   * Удаляем внешний ключ из таблицы comment_user, так как он мешает нормальному удалению комментов
   */
  public function update1_1_1()
  {
    if(count(CommentUser::model()->tableSchema->foreignKeys))
    {
      $this->startRawSql();
?>
ALTER TABLE  `comment_user` DROP FOREIGN KEY  `comment_user_ibfk_1` ;
<?php
$this->endRawSql();
        }
    }
}
