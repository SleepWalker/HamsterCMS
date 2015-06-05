<?php

namespace contest\components;

class ContestFactory extends \CApplicationComponent
{
    /**
     * @return \contest\models\view\Request
     */
    public function createRequest()
    {
        return new \contest\models\view\Request();
    }
}
