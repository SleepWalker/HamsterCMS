<?php
namespace cart\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [1, 1.1];
    }

    public function update1_1()
    {
        $this->startRawSqlIfNot("SHOW COLUMNS FROM `order_check` LIKE 'meta'");
        ?>
-- Добавляем новую колонку для доп инфы о каждом товаре (к примеру варианты товара: цвет, размер и т.д.)
ALTER TABLE `order_check` ADD `meta` TEXT;
<?php
$this->endRawSql();
    }
}
