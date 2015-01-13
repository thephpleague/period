<?php

use League\Period\Period;

class TimeRangeTest extends PHPUnit_Framework_TestCase
{
    private $timezone;

    public function setUp()
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testToString()
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = new Period('2014-05-01', '2014-05-08');
        $this->assertSame('2014-04-30T21:00:00Z/2014-05-07T21:00:00Z', (string) $period);
    }

    public function testGetDatePeriod()
    {
        $period = Period::createFromDuration(new DateTime(), "1 DAY");
        $range  = $period->getRange(3600); //TODO change to getDatePeriod on next MAJOR version
        $this->assertInstanceof('DatePeriod', $range);
        $this->assertCount(24, iterator_to_array($range));
    }

    public function testDurationAsDateInterval()
    {
        $period = Period::createFromMonth(2014, 3);
        $start  = new DateTime('2014-03-01');
        $end    = new DateTime('2014-04-01');
        $res = $period->getDuration();
        $this->assertInstanceof('DateInterval', $res);
        $this->assertEquals($start->diff($end), $period->getDuration());
    }

    public function testDurationAsSeconds()
    {
        $period = Period::createFromMonth(2014, 3);
        $start  = new DateTime('2014-03-01');
        $end    = new DateTime('2014-04-01');
        $res = $period->getDuration(true);
        $this->assertInternalType('integer', $res);
        $this->assertEquals($end->getTimestamp() - $start->getTimestamp(), $res);
    }
}
