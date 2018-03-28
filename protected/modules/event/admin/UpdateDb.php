<?php
namespace event\admin;

class UpdateDb extends \admin\components\HUpdateDb
{
    public function verHistory()
    {
        return ['1.1', '1.1.1'];
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
}
