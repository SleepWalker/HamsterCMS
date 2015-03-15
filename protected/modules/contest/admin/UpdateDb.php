<?php

class UpdateDb extends HUpdateDb
{
    public function verHistory()
    {
        return array('1.1');
    }

    /**
     * Изначальная структура
     */
    public function update1_1()
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS `contest_request` (
              `id` int(10) unsigned NOT NULL,
              `first_name` varchar(128) NOT NULL COMMENT 'Имя',
              `last_name` varchar(128) NOT NULL COMMENT 'Фамилия',
              `birthdate` date NOT NULL COMMENT 'Дата рождения',
              `email` varchar(64) NOT NULL COMMENT 'Email',
              `phone` varchar(25) DEFAULT NULL COMMENT 'Телефон',
              `type` enum('solo','group') NOT NULL,
              `instrument` varchar(64) NOT NULL COMMENT 'Инструмент',
              `school` varchar(128) DEFAULT NULL COMMENT 'Школа',
              `teacher` varchar(128) DEFAULT NULL COMMENT 'Преподаватель',
              `demos` text COMMENT 'Ссылки на демо записи',
              `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
        ");
    }
}
