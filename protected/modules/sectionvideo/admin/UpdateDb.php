<?php
namespace sectionvideo\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return ['1.1', '1.2.0', '1.2.1'];
    }

    /**
     * Таблицы для учителей, музыкантов и тд.
     * Возможность добавления нескольких музыкантов к одной композиции
     */
    public function update1_1()
    {
        // Музыканты
        $this->createTable('{{section_musician}}', array(
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => "VARCHAR(255) NOT NULL COMMENT 'Имя музыканта/коллектива'",

            'PRIMARY KEY (`id`)',
            'INDEX `index2` (`name` ASC)',
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci');

        // Инструменты
        $this->createTable('{{section_instrument}}', array(
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => "VARCHAR(255) NOT NULL COMMENT 'Имя музыканта/коллектива'",

            'PRIMARY KEY (`id`)',
            'INDEX `index2` (`name` ASC)',
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci');

        // Учителя
        $this->createTable('{{section_teacher}}', array(
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'school_id' => "INT(10) UNSIGNED NULL DEFAULT NULL",
            'first_name' => "VARCHAR(128) NOT NULL COMMENT 'Имя'",
            'last_name' => "VARCHAR(128) NOT NULL COMMENT 'Фамилия'",
            'middle_name' => "VARCHAR(128) NULL DEFAULT NULL COMMENT 'Отчество'",
            'bio' => "TEXT NULL DEFAULT NULL COMMENT 'Биография'",
            'photo' => "TEXT NULL DEFAULT NULL COMMENT 'Фото'",
            'classes' => "VARCHAR(45) NULL DEFAULT NULL COMMENT 'Преподоваемые классы'",

            'PRIMARY KEY (`id`)',
            'INDEX `fk_section_teacher_section_school1` (`school_id` ASC)',
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci');

        // Школы
        $this->createTable('{{section_school}}', array(
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => "VARCHAR(128) NOT NULL COMMENT 'Название'",
            'short_name' => "VARCHAR(45) NOT NULL COMMENT 'Короткое название'",
            'address' => "TEXT NULL DEFAULT NULL COMMENT 'Адрес'",
            'phone' => "TEXT NULL DEFAULT NULL COMMENT 'Телефоны'",
            'fax' => "TEXT NULL DEFAULT NULL COMMENT 'Факс'",
            'email' => "TEXT NULL DEFAULT NULL COMMENT 'Email'",
            'site' => "TEXT NULL DEFAULT NULL COMMENT 'Сайт'",
            'photo' => "TEXT NULL DEFAULT NULL COMMENT 'Фото/Лого'",
            'geo' => "TEXT NULL DEFAULT NULL COMMENT 'Координаты (LatLng) на карте'",

            'PRIMARY KEY (`id`)',
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci');

        $this->addForeignKey('fk_section_teacher_section_school1',
            '{{section_teacher}}', 'school_id',
            '{{section_school}}', 'id',
            'NO ACTION', 'NO ACTION');

        // Связь музыкантов с школами, учителями, видео и инструментами
        $this->createTable('{{section_video_musicians}}', array(
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'video_id' => "INT(10) UNSIGNED NOT NULL",
            'musician_id' => "INT(10) UNSIGNED NOT NULL",
            'instrument_id' => "INT(10) UNSIGNED NULL DEFAULT NULL",
            'teacher_id' => "INT(10) UNSIGNED NULL DEFAULT NULL",
            'class' => "TINYINT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Класс ученика'",
            'sort_order' => "INT UNSIGNED NOT NULL COMMENT 'Порядок отображения'",

            'PRIMARY KEY (`id`)',
            'INDEX `fk_section_video_musicians_section_musician1` (`musician_id` ASC)',
            'INDEX `fk_section_video_musicians_section_instrument1` (`instrument_id` ASC)',
            'INDEX `fk_section_video_musicians_section_teacher1` (`teacher_id` ASC)',
            'INDEX `fk_section_video_musicians_section_video1` (`video_id` ASC)',
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci');

        $this->addForeignKey('fk_section_video_musicians_section_musician1',
            '{{section_video_musicians}}', 'musician_id',
            '{{section_musician}}', 'id',
            'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_section_video_musicians_section_instrument1',
            '{{section_video_musicians}}', 'instrument_id',
            '{{section_instrument}}', 'id',
            'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_section_video_musicians_section_teacher1',
            '{{section_video_musicians}}', 'teacher_id',
            '{{section_teacher}}', 'id',
            'NO ACTION', 'NO ACTION');

        $this->addForeignKey('fk_section_video_musicians_section_video1',
            '{{section_video_musicians}}', 'video_id',
            '{{section_video}}', 'id',
            'NO ACTION', 'NO ACTION');

        $this->addColumn('{{section_video}}', 'type', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`');
        $this->addColumn('{{section_video}}', 'event_id', 'INT(10) UNSIGNED NULL DEFAULT NULL  AFTER `event`');
        $this->addColumn('{{section_video}}', 'title', 'VARCHAR(128) NULL DEFAULT NULL  AFTER `thumbnail`');

        $this->renameColumn('{{section_video}}', 'add_date', 'date_create');
        $this->alterColumn('{{section_video}}', 'description', 'TEXT NULL DEFAULT NULL');

        // обновляем данные
        $videos = $this->dbConnection->createCommand()
                       ->select('*')
                       ->from('{{section_video}}')
                       ->queryAll();

        $addedTeachers = array();
        $addedMusicians = array();
        foreach ($videos as $video) {
            if (!isset($addedMusicians[$video['musician']])) {
                $this->insert('{{section_musician}}', array(
                    'name' => $video['musician'],
                ));
                $addedMusicians[$video['musician']] = $this->dbConnection->lastInsertID;
            }
            if (!isset($addedTeachers[$video['teacher']])) {
                $this->insert('{{section_teacher}}', array(
                    'first_name' => $video['teacher'],
                ));
                $addedTeachers[$video['teacher']] = $this->dbConnection->lastInsertID;
            }

            $this->insert('{{section_video_musicians}}', array(
                'video_id' => $video['id'],
                'teacher_id' => $addedTeachers[$video['teacher']],
                'musician_id' => $addedMusicians[$video['musician']],
            ));
        }

        $events = $this->dbConnection->createCommand()
                       ->select('*')
                       ->from('{{event}}')
                       ->queryAll();

        foreach ($events as $event) {
            $this->update('{{section_video}}', array(
                'event_id' => $event['id'],
            ), 'event = "' . $event['name'] . '"');
        }

        $this->dropColumn('{{section_video}}', 'teacher');
        $this->dropColumn('{{section_video}}', 'musician');
    }

    /**
     * Переименовал rating->likes
     */
    public function update1_2_0()
    {
        $this->alterColumn('{{section_video}}', 'rating', 'INT(10) UNSIGNED NOT NULL DEFAULT 0');
        $this->renameColumn('{{section_video}}', 'rating', 'likes');
    }

    /**
     * Добавил views
     */
    public function update1_2_1()
    {
        $this->addColumn('{{section_video}}', 'views', 'INT(10) UNSIGNED NOT NULL DEFAULT 0');
    }
}
