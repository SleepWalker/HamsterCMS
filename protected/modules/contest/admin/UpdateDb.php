<?php

class UpdateDb extends HUpdateDb
{
    public function verHistory()
    {
        return array('1.1.0', '1.2.0', '1.3.0', '1.4.0');
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Разделил заявки на три сушности
     * Так же эта миграция добавляет автоинкремент к pk реквеста
     *
     * NOTE: мы не заботимся о данных, так как их нету нигде, где используется этот модуль
     */
    public function update1_2_0()
    {
        $this->createTable('{{contest_musician}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'request_id' => 'INT(11) UNSIGNED NOT NULL',
            'first_name' => "varchar(128) NOT NULL COMMENT 'Имя'",
            'last_name' => "varchar(128) NOT NULL COMMENT 'Фамилия'",
            'birthdate' => "date NOT NULL COMMENT 'Дата рождения'",
            'email' => "varchar(64) DEFAULT NULL COMMENT 'Email'",
            'phone' => "varchar(25) DEFAULT NULL COMMENT 'Телефон'",
            'instrument' => "varchar(64) NOT NULL COMMENT 'Инструмент'",
            'school' => "varchar(128) DEFAULT NULL COMMENT 'Школа'",
            'class' => "varchar(128) DEFAULT NULL COMMENT 'Класс, курс'",
            'teacher' => "varchar(128) DEFAULT NULL COMMENT 'Преподаватель'",
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createTable('{{contest_composition}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'request_id' => 'INT(11) UNSIGNED NOT NULL',
            'author' => 'VARCHAR(128) NOT NULL',
            'title' => 'VARCHAR(128) NOT NULL',
            'duration' => 'TINYINT(2) NOT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->dropTable('{{contest_request}}');
        $this->createTable('{{contest_request}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'type' => "enum('solo','group') NOT NULL",
            'format' => "TINYINT(1) UNSIGNED COMMENT 'Формат номера'",
            'name' => 'VARCHAR(128) NOT NULL COMMENT "Имя группы"',
            'demos' => "text COMMENT 'Ссылки на демо записи'",
            'date_created' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP"
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('cotest_composition_request', '{{contest_composition}}', 'request_id', '{{contest_request}}', 'id');
        $this->addForeignKey('cotest_musician_request', '{{contest_musician}}', 'request_id', '{{contest_request}}', 'id');
    }

    /**
     * Добавлен статус заявки
     */
    public function update1_3_0()
    {
        $this->addColumn('{{contest_request}}', 'status', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER id');
    }

    /**
     * Добавлен статус заявки
     */
    public function update1_4_0()
    {
        $this->addColumn('{{contest_request}}', 'meta', 'TEXT NULL AFTER status');
    }
}
