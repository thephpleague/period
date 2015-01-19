<?php

namespace League\Period\Test;

use DateInterval;
use DateTime;
use League\Period\Period;
use PHPUnit_Framework_TestCase;

class TimeRangeObjectTest extends PHPUnit_Framework_TestCase
{
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
