<?php

use contest\models\Request;

// TODO: separate test for saving Request to check that there
// is no problems with data types e.g. `status` string or int

class RequestTest extends \CTestCase
{
    public function testPassed()
    {
        $this->assertTrue(true);
    }
}
