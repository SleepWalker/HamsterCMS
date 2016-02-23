<?php
namespace user\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [0, 1, 1.1, '1.1.2'];
    }

    public function update1()
    {
        $am = Yii::app()->authManager;

        // добавляем дефолтную роль user
        $bizRule = 'return !Yii::app()->user->isGuest;';
        $am->createRole('user', 'Зарегистрированные пользователи', $bizRule);

        // добавляем дефолтную роль guest
        $bizRule = 'return Yii::app()->user->isGuest;';
        $am->createRole('guest', 'Гости', $bizRule);
    }

    public function update1_1()
    {
        $am = Yii::app()->authManager;

        // добавляем дефолтную роль transfer
        $am->removeAuthItem('transfer');
        $am->createRole('transfer', 'Пользователи, которые ожидают переноса в группу, выбранную ими при регистрации');
    }

    public function update1_1_2()
    {
        $tableName = '{{user_identity}}';

        $this->createTableIfExists($tableName, [
            'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT',
            'user_id' => 'int(11) unsigned NOT NULL',
            'provider' => 'varchar(16) NOT NULL',
            'public' => 'varchar(128) NOT NULL',
            'private' => 'varchar(128) NOT NULL',
            'PRIMARY KEY (`id`)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $users = $this->dbConnection->createCommand()->select([
            'id',
            'email',
            'password',
        ])->from('{{auth_user}}')->queryAll();

        foreach ($users as $user) {
            $this->insert($tableName, [
                'user_id' => $user['id'],
                'provider' => 'default',
                'public' => $user['email'],
                'private' => $user['password'],
            ]);
        }
    }
}
