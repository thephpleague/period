<?php

use Bakame\Tools\DateRange;

class DateRangeTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromDurationWithDateTime()
    {
        $start = new DateTime;
        $obj = DateRange::createFromDuration($start, "1 DAY");
        $this->assertEquals($obj->getStart(), $start);
        $this->assertEquals($obj->getEnd(), $start->add(new DateInterval('P1D')));
    }

    public function testCreateFromDurationWithString()
    {
        $start =  new DateTime('-1 DAY');
        $ttl = new DateInterval('P2D');
        $end = clone $start;
        $end->add($ttl);
        $obj = DateRange::createFromDuration("-1 DAY", $ttl);
        $this->assertEquals($obj->getStart(), $start);
        $this->assertEquals($obj->getEnd(), $end);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        DateRange::createFromDuration(new DateTime, "-1 DAY");
    }

    public function testMonth()
    {
        $obj = DateRange::createFromMonth(2014, 3);
        $this->assertEquals($obj->getStart(), new DateTime('2014-03-01'));
        $this->assertEquals($obj->getEnd(), new DateTime('2014-04-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testMonthFailedWithOutofRangeMonth()
    {
        DateRange::createFromMonth(2014, 32);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMonthFailedWithInvalidYear()
    {
        DateRange::createFromMonth("toto", 32);
    }

    public function testQuarter()
    {
        $obj = DateRange::createFromQuarter(2014, 3);
        $this->assertEquals($obj->getStart(), new DateTime('2014-07-01'));
        $this->assertEquals($obj->getEnd(), new DateTime('2014-10-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testQuarterFailedWithOutofRangeQuarter()
    {
        DateRange::createFromMonth(2014, -5);
    }

    public function testWeek()
    {
        $obj = DateRange::createFromWeek(2014, 3);
        $this->assertEquals($obj->getStart(), new DateTime('2014-01-13'));
        $this->assertEquals($obj->getEnd(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testWeekFailedWithUnknowWeek()
    {
        DateRange::createFromWeek(2014, -5);
    }

    public function testSemester()
    {
        $obj = DateRange::createFromSemester(2014, 2);
        $this->assertEquals($obj->getStart(), new DateTime('2014-07-01'));
        $this->assertEquals($obj->getEnd(), new DateTime('2015-01-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSemesterFailedWithOutofRangeSemester()
    {
        DateRange::createFromSemester(2014, 32);
    }

    public function testgetDatePeriod()
    {
        $obj    = DateRange::createFromDuration(new DateTime, "1 DAY");
        $period = $obj->getRange(new DateInterval('PT1H'));
        $arr    = iterator_to_array($period);
        $this->assertCount(24, $arr);
    }

    public function testsetStartReturnsANewDateRange()
    {
        $expected = new DateTime('2012-03-02');
        $obj = DateRange::createFromWeek(2014, 3);
        $res = $obj->setStart($expected);
        $this->assertEquals($res->getStart(), $expected);
        $this->assertEquals($obj->getStart(), new DateTime('2014-01-13'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testsetStartFailedWithWrongStartDate()
    {
        $obj = DateRange::createFromWeek(2014, 3);
        $obj->setStart(new DateTime('2015-03-02'));
    }

    public function testEndStartDateReturnsANewDateRange()
    {
        $expected = new DateTime('2015-03-02');
        $obj = DateRange::createFromWeek(2014, 3);
        $res = $obj->setEnd($expected);
        $this->assertEquals($res->getEnd(), $expected);
        $this->assertEquals($obj->getEnd(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testsetEndFailedWithWrongEndDate()
    {
        $obj = DateRange::createFromWeek(2014, 3);
        $obj->setEnd(new DateTime('2012-03-02'));
    }

    public function testContains()
    {
        $obj = DateRange::createFromMonth(2014, 3);
        $this->assertTrue($obj->contains(new DateTime('2014-03-12')));
        $this->assertFalse($obj->contains('2012-03-12'));
        $this->assertFalse($obj->contains('2014-04-01'));
    }

    public function testDuration()
    {
        $obj   = DateRange::createFromMonth(2014, 3);
        $start = new DateTime('2014-03-01');
        $end   = new DateTime('2014-04-01');
        $this->assertEquals($start->diff($end), $obj->getDuration());
    }

    public function testSetDuration()
    {
        $expected = DateRange::createFromMonth(2014, 3);
        $obj = DateRange::createFromDuration('2014-03-01', '2 Weeks');
        $res = $obj->setDuration('1 MONTH');
        $this->assertEquals($expected, $res);
    }
}
