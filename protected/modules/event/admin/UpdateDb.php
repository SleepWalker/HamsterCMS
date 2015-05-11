<?php

class UpdateDb extends HUpdateDb
{
    public function verHistory()
    {
        return ['1.1'];
    }

    /**
     * NULL для даты окончания
     */
    public function update1_1()
    {
        $this->alterColumn('{{event}}', 'end_date', 'DATETIME NULL');
    }
}
