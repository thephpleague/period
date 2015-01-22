<?php

namespace League\Period\Test;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use League\Period\Period;
use PHPUnit_Framework_TestCase;
use StdClass;

class PeriodTest extends PHPUnit_Framework_TestCase
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
        $range  = $period->getDatePeriod(3600);
        $this->assertInstanceof('DatePeriod', $range);
        $this->assertCount(24, iterator_to_array($range));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDatePeriodThrowsRuntimeException()
    {
        $period = Period::createFromDuration(new DateTime(), "1 DAY");
        $period->getDatePeriod(new StdClass);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetDatePeriodThrowsException()
    {
        $period = Period::createFromDuration(new DateTime(), "1 DAY");
        $period->getDatePeriod(-3600);
    }

    /**
     * @deprecated to be remove in the next MAJOR VERSION
     */
    public function testGetRange()
    {
        $period = Period::createFromDuration(new DateTime(), "1 DAY");
        $range  = $period->getRange(3600);
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

    public function testConstructor()
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $this->assertEquals(new DateTime('2014-05-01'), $period->getStart());
        $this->assertEquals(new DateTime('2014-05-08'), $period->getEnd());
    }

    /**
     * @expectedException \LogicException
     */
    public function testConstructorThrowException()
    {
        new Period(
            new DateTime('2014-05-01', new DateTimeZone('Europe/Paris')),
            new DateTime('2014-05-01', new DateTimeZone('Africa/Nairobi'))
        );
    }

    /**
     * @requires PHP 5.5
     */
    public function testConstructorWithDateTimeInterface()
    {
        $start = new DateTimeImmutable('2014-05-01');
        $end = new DateTimeImmutable('2014-05-08');
        $period = new Period($start, $end);
        $this->assertInstanceof('DateTimeInterface', $period->getStart());
        $this->assertInstanceof('DateTimeImmutable', $period->getStart());
        $this->assertEquals($start, $period->getStart());
    }

    public function testCreateFromDurationWithDateTime()
    {
        $start = new DateTime();
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

    public function testCreatFromDurationWithInteger()
    {
        $period = Period::createFromDuration('2014-01-01', 3600);
        $this->assertEquals(new DateTime('2014-01-01 01:00:00'), $period->getEnd());
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateFromDurationWithInvalidInteger()
    {
        Period::createFromDuration('2014-01-01', -1);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        Period::createFromDuration(new DateTime(), "-1 DAY");
    }

    public function testCreateFromDurationBeforeEndWithDateTime()
    {
        $end = new DateTime();
        $period = Period::createFromDurationBeforeEnd($end, "1 DAY");
        $this->assertEquals($period->getEnd(), $end);
        $this->assertEquals($period->getStart(), $end->sub(new DateInterval('P1D')));
    }

    public function testCreateFromDurationBeforeEndWithString()
    {
        $end =  new DateTime('-1 DAY');
        $ttl = new DateInterval('P2D');
        $start = clone $end;
        $start->sub($ttl);
        $period = Period::createFromDurationBeforeEnd("-1 DAY", $ttl);
        $this->assertEquals($period->getStart(), $start);
        $this->assertEquals($period->getEnd(), $end);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationBeforeEndFailedWithOutofRangeInterval()
    {
        Period::createFromDurationBeforeEnd(new DateTime(), "-1 DAY");
    }

    public function testCreateFromWeek()
    {
        $period = Period::createFromWeek(2014, 3);
        $this->assertEquals($period->getStart(), new DateTime('2014-01-13'));
        $this->assertEquals($period->getEnd(), new DateTime('2014-01-20'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromWeekFailedWithInvalidYear()
    {
        Period::createFromWeek("toto", 5);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromWeekFailedWithLowInvalidIndex()
    {
        Period::createFromWeek(2014, 0);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromWeekFailedWithHighInvalidIndex()
    {
        Period::createFromWeek(2014, 54);
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
    public function testCreateFromMonthFailedWithHighInvalidIndex()
    {
        Period::createFromMonth(2014, 13);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromMonthFailedWithLowInvalidIndex()
    {
        Period::createFromMonth(2014, 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromMonthFailedWithInvalidYear()
    {
        Period::createFromMonth("toto", 8);
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
    public function testCreateFromQuarterFailedWithHighInvalidIndex()
    {
        Period::createFromQuarter(2014, 5);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromQuarterFailedWithLowInvalidIndex()
    {
        Period::createFromQuarter(2014, 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromQuarterFailedWithInvalidYear()
    {
        Period::createFromQuarter("toto", 2);
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
    public function testCreateFromSemesterFailedWithLowInvalidIndex()
    {
        Period::createFromSemester(2014, 0);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCreateFromSemesterFailedWithHighInvalidIndex()
    {
        Period::createFromSemester(2014, 3);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromSemesterFailedWithInvalidYear()
    {
        Period::createFromSemester("toto", 1);
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

    public function testIsBeforeDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $beforeDateTime = new DateTime('2010-01-01');
        $afterDateTime = new DateTime('2015-01-01');
        $this->assertTrue($orig->isBefore($afterDateTime));
        $this->assertFalse($orig->isBefore($beforeDateTime));
    }

    public function testIsBeforePeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = Period::createFromDuration('2012-04-01', '2 MONTH');
        $this->assertTrue($orig->isBefore($alt));
        $this->assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforePeriodWithAbutsPeriods()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = $orig->next('1 HOUR');
        $this->assertTrue($orig->isBefore($alt));
    }

    public function testIsAfterDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $beforeDateTime = new DateTime('2010-01-01');
        $afterDateTime = new DateTime('2015-01-01');
        $this->assertFalse($orig->isAfter($afterDateTime));
        $this->assertTrue($orig->isAfter($beforeDateTime));
    }

    public function testIsAfterPeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = Period::createFromDuration('2012-04-01', '2 MONTH');
        $this->assertFalse($orig->isAfter($alt));
        $this->assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDatetimeAbuts()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertTrue($orig->isBefore($orig->getEnd()));
        $this->assertFalse($orig->isAfter($orig->getStart()));
    }

    public function testIsAfterPeriodWithAbutsPeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = $orig->next('1 HOUR');
        $this->assertTrue($alt->isAfter($orig));
    }

    public function testAbuts()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');
        $this->assertFalse($orig->abuts($alt));
        $this->assertTrue($alt->abuts($alt->next('1 HOUR')));
    }

    public function testContainsWithDateTime()
    {
        $period = Period::createFromMonth(2014, 3);
        $this->assertTrue($period->contains(new DateTime('2014-03-12')));
        $this->assertFalse($period->contains('2012-03-12'));
        $this->assertFalse($period->contains('2014-04-01'));
    }

    public function testContainsWithPeriod()
    {
        $orig = Period::createFromSemester(2014, 1);
        $alt  = Period::createFromQuarter(2014, 1);
        $this->assertTrue($orig->contains($alt));
        $this->assertFalse($alt->contains($orig));
    }

    public function testOverlapsFalseWithAbutsPeriods()
    {
        $orig = Period::createFromMonth(2014, 3);
        $alt = Period::createFromMonth(2014, 4);
        $this->assertFalse($orig->overlaps($alt));
    }

    public function testOverlapsFalseWithGappingPeriod()
    {
        $orig = Period::createFromMonth(2014, 3);
        $alt = Period::createFromMonth(2013, 4);
        $this->assertFalse($orig->overlaps($alt));
    }

    public function testOverlapsTrueWithPeriodWithoutSameEnding()
    {
        $orig = Period::createFromMonth(2014, 3);
        $alt  = Period::createFromDuration('2014-03-15', '3 WEEKS');
        $this->assertTrue($orig->overlaps($alt));
    }

    public function testOverlapsTrueWithPeriodsSharingSameEnding()
    {
        $orig = Period::createFromMonth(2014, 3);
        $alt  = new Period('2014-03-01', '2014-04-01');
        $this->assertTrue($orig->overlaps($alt));
    }

    public function testOverlapsTrueWithPeriodContainingAnotherPeriod()
    {
        $orig = Period::createFromMonth(2014, 3);
        $alt  = Period::createFromDuration('2014-03-13', '2014-03-15');
        $this->assertTrue($orig->overlaps($alt));
        $this->assertTrue($alt->overlaps($orig));
    }

    public function testCompareMethods()
    {
        $orig  = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt   = Period::createFromDuration('2012-01-01', '1 WEEK');
        $other = Period::createFromDuration('2013-01-01', '1 MONTH');

        $this->assertTrue($orig->durationGreaterThan($alt));
        $this->assertFalse($orig->durationLessThan($alt));
        $this->assertTrue($alt->durationLessThan($other));
        $this->assertTrue($orig->sameDurationAs($other));
    }

    public function testSameValueAsTrue()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt  = Period::createFromMonth(2012, 1);
        $this->assertTrue($orig->sameValueAs($alt));
    }

    public function testSameValueAsFalseWithSameStartingValue()
    {
        $orig  = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt   = Period::createFromDuration('2012-01-01', '1 WEEK');
        $this->assertFalse($orig->sameValueAs($alt));
    }

    public function testSameValueAsFalseWithSameEndingValue()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-01-01', '1 WEEK');
        $alt  = Period::createFromDurationBeforeEnd('2012-01-01', '1 MONTH');
        $this->assertFalse($orig->sameValueAs($alt));
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

    public function testWithDuration()
    {
        $expected = Period::createFromMonth(2014, 3);
        $period   = Period::createFromDuration('2014-03-01', '2 Weeks');
        $this->assertEquals($expected, $period->withDuration('1 MONTH'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testWithDurationThrowsException()
    {
        $period = Period::createFromDuration('2014-03-01', '2 Weeks');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDuration($interval);
    }

    public function testMerge()
    {
        $period    = Period::createFromMonth(2014, 3);
        $altPeriod = Period::createFromMonth(2014, 4);
        $expected  = Period::createFromDuration('2014-03-01', '2 MONTHS');

        $this->assertEquals($expected, $period->merge($altPeriod));
        $this->assertEquals($expected, $altPeriod->merge($period));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMergeThrowsException()
    {
        $period = Period::createFromMonth(2014, 3);
        $period->merge();
    }

    public function testAdd()
    {
        $orig   = Period::createFromDuration('2012-01-01', '1 MONTH');
        $period = $orig->add('1 MONTH');
        $this->assertTrue($period->durationGreaterThan($orig));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddThrowsLogicException()
    {
        Period::createFromDuration('2012-01-01', '1 MONTH')->add('-3 MONTHS');
    }

    public function testSub()
    {
        $orig   = Period::createFromDuration('2012-01-01', '1 MONTH');
        $period = $orig->sub('1 WEEK');
        $this->assertTrue($period->durationLessThan($orig));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSubThrowsLogicException()
    {
        Period::createFromDuration('2012-01-01', '1 MONTH')->sub('3 MONTHS');
    }

    public function testNext()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $next = $orig->next('1 WEEK');
        $this->assertEquals($next->getStart(), $orig->getEnd());
    }

    public function testNextWithoutDuration()
    {
        $orig   = Period::createFromDuration('2012-01-01', '1 MONTH');
        $period = $orig->next();
        $this->assertEquals($period->getStart(), $orig->getEnd());
    }

    public function testPrevious()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $prev = $orig->previous('1 MONTH');
        $this->assertEquals($prev->getEnd(), $orig->getStart());
    }

    public function testPreviousWithoutDuration()
    {
        $orig   = Period::createFromDuration('2012-01-01', '1 MONTH');
        $period = $orig->previous();
        $this->assertEquals($period->getEnd(), $orig->getStart());
    }

    public function testPreviousNext()
    {
        $period = Period::createFromWeek(2014, 13);
        $alt = $period->next('3 MONTH')->previous('1 WEEK');
        $this->assertTrue($period->sameValueAs($alt));
    }

    public function testDurationDiffWithDateInterval()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $res = $orig->durationDiff($alt);
        $this->assertInstanceof('\DateInterval', $res);
    }

    public function testDurationDiffWithSeconds()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $res = $orig->durationDiff($alt, true);
        $this->assertInternalType('integer', $res);
        $this->assertSame(-3600, $res);
    }

    public function testDurationDiffPositionIrrelevant()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $fromOrig = $orig->durationDiff($alt);
        $fromOrig->invert = 1;
        $fromAlt = $alt->durationDiff($orig);
        $this->assertEquals($fromOrig, $fromAlt);
    }

    public function testIntersect()
    {
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');

        $res = $orig->intersect($alt);
        $this->assertInstanceof('\League\Period\Period', $res);
    }

    /**
     * @expectedException \LogicException
     */
    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange()
    {
        $orig = Period::createFromDuration('2013-01-01', '1 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');
        $orig->intersect($alt);
    }

    /**
     * @expectedException \LogicException
     */
    public function testIntersectThrowsExceptionWithAdjacentTimeRange()
    {
        $orig = Period::createFromDuration('2013-01-01', '1 MONTH');
        $alt = $orig->next();
        $orig->intersect($alt);
    }

    public function testGap()
    {
        $orig = Period::createFromDuration('2011-12-01', '2 MONTHS');
        $alt = Period::createFromDuration('2012-06-15', '3 MONTHS');
        $resOne = $orig->gap($alt);
        $this->assertInstanceof('\League\Period\Period', $resOne);
        $this->assertEquals($orig->getEnd(), $resOne->getStart());
        $this->assertEquals($alt->getStart(), $resOne->getEnd());
        $resTwo = $alt->gap($orig);
        $this->assertTrue($resOne->sameValueAs($resTwo));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapThrowsExceptionWithOverlapsPeriod()
    {
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');

        $orig->gap($alt);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapWithSameStartingPeriod()
    {
        $orig = Period::createFromDuration('2012-12-01', '5 MONTH');
        $alt = Period::createFromDuration('2012-12-01', '2 MONTH');

        $orig->gap($alt);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapWithSameEndingPeriod()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $alt  = Period::createFromDurationBeforeEnd('2012-12-01', '2 MONTH');

        $orig->gap($alt);
    }

    public function testGapWithAdjacentPeriod()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $alt = $orig->next('1 MINUTE');

        $res = $orig->gap($alt);
        $this->assertInstanceof('\League\Period\Period', $res);
        $this->assertSame(0, $res->getDuration(true));
    }

    /**
     * @expectedException \LogicException
     */
    public function testDiffThrowsException()
    {
        Period::createFromYear(2015)->diff(Period::createFromYear(2013));
    }

    public function testDiffWithEqualsPeriod()
    {
        $period = Period::createFromYear(2013);
        $alt = Period::createFromDuration('2013-01-01', '1 YEAR');
        $this->assertCount(0, $alt->diff($period));
    }

    public function testDiffWithPeriodSharingOneEndpoints()
    {
        $period = Period::createFromYear(2013);
        $alt = Period::createFromDuration('2013-01-01', '3 MONTHS');
        $res = $alt->diff($period);
        $this->assertCount(1, $res);
        $this->assertInstanceof('League\Period\Period', $res[0]);
        $this->assertEquals(new Datetime('2013-04-01'), $res[0]->getStart());
        $this->assertEquals(new Datetime('2014-01-01'), $res[0]->getEnd());
    }

    public function testDiffWithOverlapsPeriod()
    {
        $period = Period::createFromDuration('2013-01-01 10:00:00', '3 HOURS');
        $alt = Period::createFromDuration('2013-01-01 11:00:00', '3 HOURS');
        $res = $alt->diff($period);
        $this->assertCount(2, $res);
        $this->assertEquals(3600, $res[1]->getDuration(true));
        $this->assertEquals(3600, $res[0]->getDuration(true));
    }
}
