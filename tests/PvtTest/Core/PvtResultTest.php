<?php

namespace PvtTest\Core;

use Pvt\Core\PvtResult;

class PvtResultTest extends \PvtTest\PvtTestCase
{
    public function testConstructorConfiguresUserId()
    {
        $result = new PvtResult(10001, 1356868376);
        $this->assertEquals(10001, $result->userId());
    }

    public function testConstructorConfiguresDateTimeAsUTC()
    {
        $result = new PvtResult(10001, 1356868376);
        $this->assertEquals(1356868376, $result->date()->getTimestamp());
        $this->assertEquals('2012-12-30T11:52:56+0000', $result->date()->format(\DateTime::ISO8601));
    }

    public function testDateIsImmutable()
    {
        $result = new PvtResult(10001, 1356868376);
        $date = $result->date();
        $date->add(new \DateInterval('PT10H'));
        $this->assertEquals(1356868376, $result->date()->getTimestamp());
    }

    public function testConstructorConfiguresResponses()
    {
        $result = new PvtResult(
            10001,
            1356868376,
            array(
                402.50,
                323.87,
                327.90,
                478.91,
                398.63
            )
        );
        $this->assertEquals(
            array(
                402.50,
                323.87,
                327.90,
                478.91,
                398.63
            ),
            $result->responses()
        );
    }

    public function testAverageResponseTimeIsNullByDefault()
    {
        $result = new PvtResult(10001, 1356868376);
        $this->assertNull($result->averageResponseTime());
    }

    public function testAverageResponseTimeOfOneTestIsTime()
    {
        $result = new PvtResult(10001, 1356868376, array(493.45));
        $this->assertEquals(493.45, $result->averageResponseTime());
    }

    public function testAverageResponseTimeOfMultipleTestsIsAverage()
    {
        $result = new PvtResult(
            10001,
            1356868376,
            array(
                402.50,
                323.87,
                327.90,
                478.91,
                398.63
            )
        );
        $this->assertEquals(386.362, $result->averageResponseTime());
    }

    public function testErrorsZeroByDefault()
    {
        $result = new PvtResult(10001, 1356868376);
        $this->assertTrue($result->errors() === 0);
    }

    public function testResponseTimes500msAndOverAreConsideredErrors()
    {
        $result = new PvtResult(10001, 1356868376, array(100));
        $this->assertEquals(0, $result->errors());
        
        $result = new PvtResult(10001, 1356868376, array(200));
        $this->assertEquals(0, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(400));
        $this->assertEquals(0, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(499.99));
        $this->assertEquals(0, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(500));
        $this->assertEquals(1, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(500.01));
        $this->assertEquals(1, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(600));
        $this->assertEquals(1, $result->errors());

        $result = new PvtResult(10001, 1356868376, array(1000));
        $this->assertEquals(1, $result->errors());

        $result = new PvtResult(
            10001,
            1356868376,
            array(
                100.00,
                200.00,
                300.00,
                400.00,
                500.00,
                600.00,
                700.00,
                800.00,
                900.00,
                1000.00
            )
        );
        $this->assertEquals(6, $result->errors());
    }

    public function testReportUrlIsMadeUpOfUserAndTimestamp()
    {
        $result = new PvtResult(10001, 1234567890);
        $this->assertEquals('/users/10001/report/1234567890', $result->reportUrl());
    }
}
