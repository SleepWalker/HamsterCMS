<?php
namespace event\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return [
            '1.1',
            '1.1.1',
            '1.2.0',
            '1.2.1',
        ];
    }

    /**
     * NULL для даты окончания
     */
    public function update1_1()
    {
        $this->alterColumn('{{event}}', 'end_date', 'DATETIME NULL');
    }

    /**
     * Swap lng and lat column values
     */
    public function update1_1_1()
    {
        \Yii::app()->db
            ->createCommand(
                'UPDATE {{event}} SET longitude=(@temp:=longitude), longitude = latitude, latitude = @temp;'
            )->execute();

    }

    /**
     * Add event images
     */
    public function update1_2_0()
    {
        $this->addColumn(
            '{{event}}',
            'image_id',
            'INT(11) UNSIGNED DEFAULT NULL'
        );
    }

    public function update1_2_1()
    {
        $this->alterColumn('{{event}}', 'image_id', 'INT(11) UNSIGNED DEFAULT NULL');
    }
}
