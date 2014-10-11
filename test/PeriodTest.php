<?php

use League\Period\Period;

class PeriodTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $this->assertEquals(new DateTime('2014-05-01'), $period->getStart());
        $this->assertEquals(new DateTime('2014-05-08'), $period->getEnd());
    }

    public function testCreateFromDurationWithDateTime()
    {
        $start = new DateTime;
        $period = Period::createFromDuration($start, "1 DAY");
        $this->assertEquals($period->getStart(), $start);
        $this->assertEquals($period->getEnd(), $start->add(new DateInterval('P1D')));
    }

    public function testCreateFromDurationWithString()
    {
        $start =  new DateTime('-1 DAY');
        $ttl = new DateInterval('P2D');
        $end = clone $start;
        $end->add($ttl);
        $period = Period::createFromDuration("-1 DAY", $ttl);
        $this->assertEquals($period->getStart(), $start);
        $this->assertEquals($period->getEnd(), $end);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        Period::createFromDuration(new DateTime, "-1 DAY");
    }

    public function testCreateFromWeek()
    {
        $period = Period::createFromWeek(2014, 3);
        $this->assertEquals($period->getStart(), new DateTime('2014-01-13'));
        $this->assertEquals($period->getEnd(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromWeekFailedWithUnknowWeek()
    {
        Period::createFromWeek(2014, -5);
    }

    public function testCreateFromMonth()
    {
        $period = Period::createFromMonth(2014, 3);
        $this->assertEquals($period->getStart(), new DateTime('2014-03-01'));
        $this->assertEquals($period->getEnd(), new DateTime('2014-04-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromMonthFailedWithOutofRangeMonth()
    {
        Period::createFromMonth(2014, 32);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromMonthFailedWithInvalidYear()
    {
        Period::createFromMonth("toto", 32);
    }

    public function testCreateFromQuarter()
    {
        $period = Period::createFromQuarter(2014, 3);
        $this->assertEquals($period->getStart(), new DateTime('2014-07-01'));
        $this->assertEquals($period->getEnd(), new DateTime('2014-10-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromQuarterFailedWithOutofRangeQuarter()
    {
        Period::createFromMonth(2014, -5);
    }

    public function testCreateFromSemester()
    {
        $period = Period::createFromSemester(2014, 2);
        $this->assertEquals($period->getStart(), new DateTime('2014-07-01'));
        $this->assertEquals($period->getEnd(), new DateTime('2015-01-01'));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromSemesterFailedWithOutofRangeSemester()
    {
        Period::createFromSemester(2014, 32);
    }

    public function testCreateFromYear()
    {
        $period = Period::createFromYear(2014);
        $this->assertEquals($period->getStart(), new DateTime('2014-01-01'));
        $this->assertEquals($period->getEnd(), new DateTime('2015-01-01'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromYearFailedWithInvalidYear()
    {
        Period::createFromYear("toto");
    }

    public function testGetRange()
    {
        $period = Period::createFromDuration(new DateTime, "1 DAY");
        $range  = $period->getRange(new DateInterval('PT1H'));
        $this->assertCount(24, iterator_to_array($range));
    }

    public function testStartingOnReturnsNewPeriod()
    {
        $expected  = new DateTime('2012-03-02');
        $period    = Period::createFromWeek(2014, 3);
        $newPeriod = $period->startingOn($expected);
        $this->assertEquals($newPeriod->getStart(), $expected);
        $this->assertEquals($period->getStart(), new DateTime('2014-01-13'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testStartingOnFailedWithWrongStartDate()
    {
        $period = Period::createFromWeek(2014, 3);
        $period->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOnReturnsNewPeriod()
    {
        $expected  = new DateTime('2015-03-02');
        $period    = Period::createFromWeek(2014, 3);
        $newPeriod = $period->endingOn($expected);
        $this->assertEquals($newPeriod->getEnd(), $expected);
        $this->assertEquals($period->getEnd(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testEndingOnFailedWithWrongEndDate()
    {
        $period = Period::createFromWeek(2014, 3);
        $period->endingOn(new DateTime('2012-03-02'));
    }

    public function testContains()
    {
        $period = Period::createFromMonth(2014, 3);
        $this->assertTrue($period->contains(new DateTime('2014-03-12')));
        $this->assertFalse($period->contains('2012-03-12'));
        $this->assertFalse($period->contains('2014-04-01'));
    }

    public function testDuration()
    {
        $period = Period::createFromMonth(2014, 3);
        $start  = new DateTime('2014-03-01');
        $end    = new DateTime('2014-04-01');
        $this->assertEquals($start->diff($end), $period->getDuration());
    }

    public function testUsingTimestampAsInterval()
    {
        $period1 = Period::createFromDuration('2014-03-12', '1 HOUR');
        $period2 = Period::createFromDuration('2014-03-12', 3600);
        $this->assertEquals($period1->getEnd(), $period2->getEnd());

    }

    public function testSetDuration()
    {
        $expected = Period::createFromMonth(2014, 3);
        $period   = Period::createFromDuration('2014-03-01', '2 Weeks');
        $this->assertEquals($expected, $period->withDuration('1 MONTH'));
    }

    public function testOverlaps()
    {
        $period1 = Period::createFromMonth(2014, 3);
        $period2 = Period::createFromMonth(2014, 4);
        $period3 = Period::createFromDuration('2014-03-15', '3 WEEKS');

        $this->assertFalse($period1->overlaps($period2));
        $this->assertTrue($period1->overlaps($period3));
        $this->assertTrue($period2->overlaps($period3));
    }

    public function testMerge()
    {
        $period    = Period::createFromMonth(2014, 3);
        $altPeriod = Period::createFromMonth(2014, 4);
        $expected  = Period::createFromDuration('2014-03-01', '2 MONTHS');

        $this->assertEquals($expected, $period->merge($altPeriod));
        $this->assertEquals($expected, $altPeriod->merge($period));
    }

    public function testCompareMethods()
    {
        $orig  = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt   = Period::createFromDuration('2012-01-01', '1 WEEK');
        $other = Period::createFromDuration('2013-01-01', '1 MONTH');
        $same  = Period::createFromMonth(2012, 1);

        $this->assertTrue($orig->durationGreaterThan($alt));
        $this->assertFalse($orig->durationLessThan($alt));
        $this->assertTrue($alt->durationLessThan($other));
        $this->assertTrue($orig->sameDurationAs($other));
        $this->assertFalse($orig->sameValueAs($other));
        $this->assertTrue($orig->sameValueAs($same));
    }
}
