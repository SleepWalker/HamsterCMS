<?php

class RatingTest extends \CTestCase
{
    public function testCalculateRating()
    {
        $updater = $this->getMockBuilder('\sectionvideo\components\RatingCalculator')
                        ->setMethods(['getLocalRating', 'getExternalRating'])
                        ->getMock()
                        ;

        $updater->expects($this->once())
                ->method('getLocalRating')
                ->with($this->equalTo('foo'))
                ->will($this->returnValue(1))
                ;

        $updater->expects($this->once())
                ->method('getExternalRating')
                ->with($this->equalTo('bar'))
                ->will($this->returnValue(2))
                ;

        $actual = $updater->calculateRating('foo', 'bar');

        $this->assertEquals(3, $actual);
    }
}
