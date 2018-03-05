<?php
namespace contest\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [
            '1.1.0',
            '1.2.0',
            '1.3.0',
            '1.4.0',
            '1.5.0',
            '1.5.1',
            '1.6.0',
            '1.6.1',
            '1.6.2',
        ];
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
     * Добавлено поле для дополнительных данных
     */
    public function update1_4_0()
    {
        $this->addColumn('{{contest_request}}', 'meta', 'TEXT NULL AFTER status');
    }

    /**
     * Добавлены контактные данные, выбор категории, а так же привязка к конкретному конскурсу
     */
    public function update1_5_0()
    {
        $this->addColumn('{{contest_request}}', 'contact_name', 'VARCHAR(128) NOT NULL AFTER format');
        $this->addColumn('{{contest_request}}', 'contact_phone', 'VARCHAR(64) NOT NULL AFTER contact_name');
        $this->addColumn('{{contest_request}}', 'contact_email', 'VARCHAR(25) NOT NULL AFTER contact_phone');
        $this->addColumn('{{contest_request}}', 'contest_id', 'INT(11) UNSIGNED NOT NULL AFTER id');
        $this->addColumn('{{contest_request}}', 'age_category', 'TINYINT(1) UNSIGNED AFTER format');
    }

    public function update1_5_1()
    {
        $this->update('{{contest_request}}', [
            'contest_id' => 1
        ], 'date_Created < NOW()');
    }

    /**
     * Create table for contest management
     */
    public function update1_6_0()
    {
        $this->createTable('{{contest_contest}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'title' => 'VARCHAR(128) NOT NULL COMMENT "Название конкурса"',
            'isActive' => "enum('1','0') NOT NULL DEFAULT '0'",
            'dateCreated' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Add settings table and type column for contests
     * add previous contests
     */
    public function update1_6_1()
    {
        $this->createTable('{{contest_store}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'key' => 'VARCHAR(128) NOT NULL',
            'value' => "TEXT",
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addColumn('{{contest_contest}}', 'type', 'enum("contest", "festival") NOT NULL DEFAULT "contest" AFTER title');
        $this->dropColumn('{{contest_contest}}', 'isActive');

        $this->insert('{{contest_store}}', [
            'id' => 1,
            'key' => 'settings',
            'value' => '{}',
        ]);

        $this->insert('{{contest_contest}}', [
            'id' => 1,
            'type' => 'contest',
            'title' => 'Рок єднає нас 2015',
        ]);
        $this->insert('{{contest_contest}}', [
            'id' => 2,
            'type' => 'contest',
            'title' => 'Рок єднає нас 2016',
        ]);
        $this->insert('{{contest_contest}}', [
            'id' => 3,
            'type' => 'festival',
            'title' => 'Рок єднає нас 2017',
        ]);
    }

    /**
     * Add applicationStartDate, applicationEndDate
     */
    public function update1_6_2()
    {
        $this->addColumn('{{contest_contest}}', 'applicationStartDate', 'timestamp NULL AFTER type');
        $this->addColumn('{{contest_contest}}', 'applicationEndDate', 'timestamp NULL AFTER type');
    }
}
