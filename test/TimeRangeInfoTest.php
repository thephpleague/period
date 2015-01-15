<?php

namespace League\Period\Test;

use DateTime;
use League\Period\Period;
use PHPUnit_Framework_TestCase;

class TimeRangeInfoTest extends PHPUnit_Framework_TestCase
{
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
}
