<?php

class VideoImageTest extends \CTestCase
{
    public function testCalculateRating()
    {
        // very simple test (requires http)
        $this->assertContains('http', (new \sectionvideo\components\VideoImage('0lJmOl0kV8s'))->get());
    }
}
