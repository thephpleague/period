<?php

use Bakame\Tools\ReportingPeriod;

class ReportingPeriodTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromDuration()
    {
        $obj = ReportingPeriod::createFromDuration(new DateTime, "1 DAY");
        $this->assertEquals($obj->getStartDate(), new DateTime);
        $this->assertEquals($obj->getEndDate(), new DateTime('+1 DAY'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        ReportingPeriod::createFromDuration(new DateTime, "-1 DAY");
    }

    public function testMonth()
    {
        $obj = ReportingPeriod::createFromMonth(2014, 3);
        $this->assertEquals($obj->getStartDate(), new DateTime('2014-03-01'));
        $this->assertEquals($obj->getEndDate(), new DateTime('2014-04-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testMonthFailedWithOutofRangeMonth()
    {
        ReportingPeriod::createFromMonth(2014, 32);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMonthFailedWithInvalidYear()
    {
        ReportingPeriod::createFromMonth("toto", 32);
    }

    public function testQuarter()
    {
        $obj = ReportingPeriod::createFromQuarter(2014, 3);
        $this->assertEquals($obj->getStartDate(), new DateTime('2014-06-01'));
        $this->assertEquals($obj->getEndDate(), new DateTime('2014-09-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testQuarterFailedWithOutofRangeQuarter()
    {
        ReportingPeriod::createFromMonth(2014, -5);
    }

    public function testWeek()
    {
        $obj = ReportingPeriod::createFromWeek(2014, 3);
        $this->assertEquals($obj->getStartDate(), new DateTime('2014-01-13'));
        $this->assertEquals($obj->getEndDate(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testWeekFailedWithUnknowWeek()
    {
        ReportingPeriod::createFromWeek(2014, -5);
    }

    public function testgetPeriod()
    {
        $obj    = ReportingPeriod::createFromDuration(new DateTime, "1 DAY");
        $period = $obj->getPeriod(new DateInterval('PT1H'));
        $arr    = iterator_to_array($period);
        $this->assertCount(24, $arr);
    }

    public function testSetStartDateReturnsANewReportingPeriod()
    {
        $expected = new DateTime('2012-03-02');
        $obj = ReportingPeriod::createFromWeek(2014, 3);
        $res = $obj->setStartDate($expected);
        $this->assertEquals($res->getStartDate(), $expected);
        $this->assertEquals($obj->getStartDate(), new DateTime('2014-01-13'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetStartDateFailedWithWrongStartDate()
    {
        $obj = ReportingPeriod::createFromWeek(2014, 3);
        $obj->setStartDate(new DateTime('2015-03-02'));
    }

    public function testEndStartDateReturnsANewReportingPeriod()
    {
        $expected = new DateTime('2015-03-02');
        $obj = ReportingPeriod::createFromWeek(2014, 3);
        $res = $obj->setEndDate($expected);
        $this->assertEquals($res->getEndDate(), $expected);
        $this->assertEquals($obj->getEndDate(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetEndDateFailedWithWrongEndDate()
    {
        $obj = ReportingPeriod::createFromWeek(2014, 3);
        $obj->setEndDate(new DateTime('2012-03-02'));
    }
}
