<?php
namespace api\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return ['1.0.0'];
    }

    public function update1_0_0()
    {
        $this->createTable('{{uploads}}', [
            'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'namespace' => 'varchar(40) NOT NULL',
            'name' => 'varchar(256) NOT NULL',
            'origName' => 'varchar(256) NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('uploads_namespace', '{{uploads}}', 'namespace');
    }
}
