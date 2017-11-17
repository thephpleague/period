<?php

namespace League\Period\Test;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use JsonSerializable;
use League\Period\Period;
use PHPUnit\Framework\TestCase;

class ExtendedDate extends DateTimeImmutable
{
    public static function createFromFormat($format, $time, $timezone = null)
    {
        if (!is_object($timezone) || !$timezone instanceof DateTimeZone) {
            $timezone = date_default_timezone_get();
        }

        $datetime = parent::createFromFormat($format, $time, $timezone);

        return new self($datetime->format('Y-m-d H:i:s.u'), $timezone);
    }
}

class PeriodTest extends TestCase
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

    public function testJsonSerialize()
    {
        $period = Period::createFromMonth(2015, 4);
        $this->assertInstanceof(JsonSerializable::class, $period);
        $res = json_decode(json_encode($period));

        $this->assertEquals(
            $period->getStartDate(),
            new DateTimeImmutable($res->startDate->date, new DateTimeZone($res->startDate->timezone))
        );

        $this->assertEquals(
            $period->getEndDate(),
            new DateTimeImmutable($res->endDate->date, new DateTimeZone($res->endDate->timezone))
        );
    }

    /**
     * @dataProvider provideGetDatePeriodData
     */
    public function testGetDatePeriod($interval, $option, $count)
    {
        $period = Period::createFromDuration(new DateTime(), '1 DAY');
        $range = $period->getDatePeriod($interval, $option);
        $this->assertInstanceof(DatePeriod::class, $range);
        $this->assertCount($count, iterator_to_array($range));
    }

    public function provideGetDatePeriodData()
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), 0, 24],
            'useString' => ['2 HOUR', 0, 12],
            'useInt' => [9600, 0, 9],
            'useFloat' => [14400.0, 0, 6],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), DatePeriod::EXCLUDE_START_DATE, 23],
            'exclude start date useString' => ['2 HOUR', DatePeriod::EXCLUDE_START_DATE, 11],
            'exclude start date useInt' => [9600, DatePeriod::EXCLUDE_START_DATE, 8],
            'exclude start date useFloat' => [14400.0, DatePeriod::EXCLUDE_START_DATE, 5],
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testGetDatePeriodThrowsException()
    {
        Period::createFromDuration(new DateTime(), '1 DAY')->getDatePeriod(-3600);
    }

    public function testGetDateInterval()
    {
        $period = Period::createFromMonth(2014, 3);
        $this->assertInstanceof(DateInterval::class, $period->getDateInterval());
    }

    public function testGetTimestampInterval()
    {
        $period = Period::createFromMonth(2014, 3);
        $this->assertInternalType('int', $period->getTimestampInterval());
    }

    public function testSplit()
    {
        $period = Period::createFromDuration(new DateTime(), '1 DAY');
        $range = $period->split(3600);
        $this->assertInstanceof(Generator::class, $range);
        foreach ($range as $innerPeriod) {
            $this->assertInstanceof(Period::class, $innerPeriod);
        }
    }

    public function testSplitMustRecreateParentObject()
    {
        $period = Period::createFromDuration(new DateTime(), '1 DAY');
        $range  = $period->split(3600);
        $total = null;
        foreach ($range as $part) {
            if (is_null($total)) {
                $total = $part;
                continue;
            }
            $total = $total->merge($part);
        }
        $this->assertEquals($period, $total);
    }


    public function testSplitWithLargeInterval()
    {
        $period = Period::createFromDuration(new DateTime(), '1 DAY');
        $range  = $period->split('2 DAY');
        //HHVM bug fix
        if (defined('HHVM_VERSION')) {
            $range->next();
        }
        $this->assertEquals($period, $range->current());
    }

    public function testSplitWithInconsistentInterval()
    {
        $period = Period::createFromDuration('2010-01-01', '1 DAY');
        $range = iterator_to_array($period->split('10 HOURS'));
        $last = array_pop($range);
        $this->assertEquals(14400, $last->getTimestampInterval());
    }

    public function testSplitData()
    {
        $period = Period::createFromDuration(new DateTime('2015-01-01'), '3 days');
        $range = $period->split('1 day');
        $result = array_map(function (Period $range) {
            return [
                'start' => $range->getStartDate()->format('Y-m-d H:i:s'),
                'end'   => $range->getEndDate()->format('Y-m-d H:i:s'),
            ];
        }, iterator_to_array($range));
        $expected = [
            [
                'start' => '2015-01-01 00:00:00',
                'end'   => '2015-01-02 00:00:00',
            ],
            [
                'start' => '2015-01-02 00:00:00',
                'end'   => '2015-01-03 00:00:00',
            ],
            [
                'start' => '2015-01-03 00:00:00',
                'end'   => '2015-01-04 00:00:00',
            ],
        ];
        $this->assertSame($expected, $result);
    }

    public function testSplitDataBackwards()
    {
        $period = Period::createFromDuration(new DateTime('2015-01-01'), '3 days');
        $range = $period->splitBackwards('1 day');
        $result = array_map(function (Period $range) {
            return [
                'start' => $range->getStartDate()->format('Y-m-d H:i:s'),
                'end'   => $range->getEndDate()->format('Y-m-d H:i:s'),
            ];
        }, iterator_to_array($range));
        $expected = [
            [
                'start' => '2015-01-03 00:00:00',
                'end'   => '2015-01-04 00:00:00',
            ],
            [
                'start' => '2015-01-02 00:00:00',
                'end'   => '2015-01-03 00:00:00',
            ],
            [
                'start' => '2015-01-01 00:00:00',
                'end'   => '2015-01-02 00:00:00',
            ],
        ];
        $this->assertSame($expected, $result);
    }

    public function testSplitBackwardsWithInconsistentInterval()
    {
        $period = Period::createFromDuration('2010-01-01', '1 DAY');
        $range = iterator_to_array($period->splitBackwards('10 HOURS'));
        $last = array_pop($range);
        $this->assertEquals(14400, $last->getTimestampInterval());
    }

    public function testSetState()
    {
        if (!method_exists(DateTimeImmutable::class, '__set_state')) {
            $this->markTestSkipped('DateTimeImmutable::__set_state is not implemented in HHVM');
        }
        $period = new Period('2014-05-01', '2014-05-08');
        $generatedPeriod = eval('return '.var_export($period, true).';');
        $this->assertTrue($generatedPeriod->sameValueAs($period));
    }

    public function testConstructor()
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $start = $period->getStartDate();
        $this->assertEquals(new DateTimeImmutable('2014-05-01'), $start);
        $this->assertEquals(new DateTimeImmutable('2014-05-08'), $period->getEndDate());
        $this->assertInstanceof(DateTimeImmutable::class, $start);
    }

    public function testConstructorWithMicroSecondsSucceed()
    {
        $period = new Period('2014-05-01 00:00:00', '2014-05-01 00:00:00');
        $this->assertEquals(new DateInterval('PT0S'), $period->getDateInterval());
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

    public function testConstructorWithDateTimeInterface()
    {
        $period = new Period('2014-05-01', new DateTime('2014-05-08'));
        $this->assertInstanceof(DateTimeImmutable::class, $period->getEndDate());
        $this->assertInstanceof(DateTimeImmutable::class, $period->getStartDate());
    }

    /**
     * @dataProvider provideCreateFromDurationData
     */
    public function testCreateFromDuration($startDate, $endDate, $duration)
    {
        $period = Period::createFromDuration($startDate, $duration);
        $this->assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        $this->assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideCreateFromDurationData()
    {
        return [
            'usingAString' => [
                '2015-01-01', '2015-01-02', '+1 DAY',
            ],
            'usingAnInt' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600,
            ],
            'usingADateInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', new DateInterval('PT1H'),
            ],
            'usingAFloatWithNoMicroseconds' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600.0,
            ],
        ];
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
        Period::createFromDuration(new DateTime(), '-1 DAY');
    }

    /**
     * @dataProvider provideCreateFromDurationBeforeEndData
     */
    public function testCreateFromDurationBeforeEnd($startDate, $endDate, $duration)
    {
        $period = Period::createFromDurationBeforeEnd($endDate, $duration);
        $this->assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        $this->assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideCreateFromDurationBeforeEndData()
    {
        return [
            'usingAString' => [
                '2015-01-01', '2015-01-02', '+1 DAY',
            ],
            'usingAnInt' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600,
            ],
            'usingADateInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', new DateInterval('PT1H'),
            ],
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFromDurationBeforeEndFailedWithOutofRangeInterval()
    {
        Period::createFromDurationBeforeEnd(new DateTime(), '-1 DAY');
    }

    public function testCreateFromWeek()
    {
        $period = Period::createFromWeek(2014, 3);
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-01-13'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2014-01-20'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromWeekFailedWithInvalidYear()
    {
        Period::createFromWeek('toto', 5);
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
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-03-01'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2014-04-01'));
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
        Period::createFromMonth('120 toto', 8);
    }

    public function testCreateFromQuarter()
    {
        $period = Period::createFromQuarter(2014, 3);
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-07-01'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2014-10-01'));
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
        Period::createFromQuarter('toto', 2);
    }

    public function testCreateFromSemester()
    {
        $period = Period::createFromSemester(2014, 2);
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-07-01'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2015-01-01'));
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
        Period::createFromSemester('toto', 1);
    }

    public function testCreateFromYear()
    {
        $period = Period::createFromYear(2014);
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-01-01'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2015-01-01'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromYearFailedWithInvalidYear()
    {
        Period::createFromYear('toto');
    }

    public function testCreateFromDay()
    {
        $period = Period::createFromDay('2015-01-03 08:06:25.235');
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2015-01-03'));
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2015-01-04'));
    }

    public function testCreateFromDayPreserveTimezone()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('DateTimeImmutable::createFromFormat is buggy in HHVM');
        }

        $period = Period::createFromDay('2008-07-01T22:35:17+08:00');
        $this->assertEquals('+08:00', $period->getStartDate()->format('P'));
        $this->assertEquals('+08:00', $period->getEndDate()->format('P'));
    }

    public function testCreateFromDayPreserveInstance()
    {
        $today = new ExtendedDate('NOW');
        $period = Period::createFromDay($today);
        $this->assertInstanceof(ExtendedDate::class, $period->getStartDate());
        $this->assertInstanceof(ExtendedDate::class, $period->getEndDate());
    }

    public function testIsBeforeDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertTrue($orig->isBefore(new DateTime('2015-01-01')));
        $this->assertFalse($orig->isBefore(new DateTime('2010-01-01')));
    }

    public function testIsBeforePeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt  = Period::createFromDuration('2012-04-01', '2 MONTH');
        $this->assertTrue($orig->isBefore($alt));
        $this->assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforePeriodWithAbutsPeriods()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertTrue($orig->isBefore(Period::createFromDuration('2012-02-01', new DateInterval('PT1H'))));
    }

    public function testIsAfterDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertFalse($orig->isAfter(new DateTime('2015-01-01')));
        $this->assertTrue($orig->isAfter(new DateTime('2010-01-01')));
    }

    public function testIsAfterPeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt  = Period::createFromDuration('2012-04-01', '2 MONTH');
        $this->assertFalse($orig->isAfter($alt));
        $this->assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDatetimeAbuts()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertTrue($orig->isBefore($orig->getEndDate()));
        $this->assertFalse($orig->isAfter($orig->getStartDate()));
    }

    public function testIsAfterPeriodWithAbutsPeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt = $orig->next('1 HOUR');
        $this->assertTrue($alt->isAfter($orig));
    }

    /**
     * @dataProvider provideAbutsData
     */
    public function testAbuts(Period $period, Period $arg, $expected)
    {
        $this->assertSame($expected, $period->abuts($arg));
    }

    public function provideAbutsData()
    {
        return [
            'testAbutsReturnsTrueWithEqualDatePoints' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-02-01', '2 MONTH'),
                true,
            ],
            'testAbutsReturnsFalseWithoutEqualDatePoints' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '2 MONTH'),
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideOverlapsData
     */
    public function testOverlaps(Period $period, Period $arg, $expected)
    {
        $this->assertSame($expected, $period->overlaps($arg));
    }

    public function provideOverlapsData()
    {
        return [
            'testOverlapsReturnsFalseWithAbutsPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromMonth(2014, 4),
                false,
            ],
            'testContainsReturnsFalseWithGappedPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromMonth(2013, 4),
                false,
            ],
            'testOverlapsReturnsTrue' => [
                Period::createFromMonth(2014, 3),
                Period::createFromDuration('2014-03-15', '3 WEEKS'),
                true,
            ],
            'testOverlapsReturnsTureWithSameDatepointsPeriods' => [
                Period::createFromMonth(2014, 3),
                new Period('2014-03-01', '2014-04-01'),
                true,
            ],
            'testOverlapsReturnsTrueContainedPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromDuration('2014-03-13', '2014-03-15'),
                true,
            ],
            'testOverlapsReturnsTrueContainedPeriodsBackward' => [
                Period::createFromDuration('2014-03-13', '2014-03-15'),
                Period::createFromMonth(2014, 3),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideContainsData
     */
    public function testContains(Period $period, $arg, $expected)
    {
        $this->assertSame($expected, $period->contains($arg));
    }

    public function provideContainsData()
    {
        return [
            'testContainsReturnsTrueWithADateTimeInterfaceObject' => [
                Period::createFromMonth(2014, 3),
                new DateTime('2014-03-12'),
                true,
            ],
            'testContainsReturnsTrueWithPeriodObject' => [
                Period::createFromSemester(2014, 1),
                Period::createFromQuarter(2014, 1),
                true,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObject' => [
                Period::createFromMonth(2014, 3),
                new DateTime('2015-03-12'),
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectAfterPeriod' => [
                Period::createFromMonth(2014, 3),
                '2012-03-12',
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectBeforePeriod' => [
                Period::createFromMonth(2014, 3),
                '2014-04-01',
                false,
            ],
            'testContainsReturnsFalseWithAbutsPeriods' => [
                Period::createFromQuarter(2014, 1),
                Period::createFromSemester(2014, 1),
                false,
            ],
            'testContainsReturnsTrueWithPeriodObjectWhichShareTheSameEndDate' => [
                Period::createFromYear(2015),
                Period::createFromMonth(2015, 12),
                true,
            ],
            'testContainsReturnsTrueWithAZeroDurationObject' => [
                new Period('2012-03-12', '2012-03-12'),
                '2012-03-12',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideCompareDurationData
     */
    public function testCompareDuration(Period $period1, Period $period2, $method, $expected)
    {
        $this->assertSame($expected, $period1->$method($period2));
    }

    public function provideCompareDurationData()
    {
        return [
            'testDurationLessThan' => [
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                Period::createFromDuration('2013-01-01', '1 MONTH'),
                'durationLessThan',
                true,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                'durationGreaterThan',
                true,
            ],
            'testSameDurationAsReturnsTrueWithMicroseconds' => [
                new Period('2012-01-01 00:00:00', '2012-01-03 00:00:00'),
                new Period('2012-02-02 00:00:00', '2012-02-04 00:00:00'),
                'sameDurationAs',
                true,
            ],
            'testSameValueAsReturnsTrue' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromMonth(2012, 1),
                'sameValueAs',
                true,
            ],
            'testSameValueAsReturnsFalse' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                'sameValueAs',
                false,
            ],
            'testSameValueAsReturnsFalseArgumentOrderIndependent' => [
                Period::createFromDurationBeforeEnd('2012-01-01', '1 WEEK'),
                Period::createFromDurationBeforeEnd('2012-01-01', '1 MONTH'),
                'sameValueAs',
                false,
            ],
        ];
    }

    public function testStartingOn()
    {
        $expected  = new DateTime('2012-03-02');
        $period = Period::createFromWeek(2014, 3);
        $newPeriod = $period->startingOn($expected);
        $this->assertTrue($newPeriod->getStartDate() == $expected);
        $this->assertEquals($period->getStartDate(), new DateTimeImmutable('2014-01-13'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testStartingOnFailedWithWrongStartDate()
    {
        $period = Period::createFromWeek(2014, 3);
        $period->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOn()
    {
        $expected  = new DateTime('2015-03-02');
        $period = Period::createFromWeek(2014, 3);
        $newPeriod = $period->endingOn($expected);
        $this->assertTrue($newPeriod->getEndDate() == $expected);
        $this->assertEquals($period->getEndDate(), new DateTimeImmutable('2014-01-20'));
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
        $period = Period::createFromDuration('2014-03-01', '2 Weeks');
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


    public function testWithDurationBeforeEnd()
    {
        $expected = Period::createFromMonth(2014, 2);
        $period = Period::createFromDurationBeforeEnd('2014-03-01', '2 Weeks');
        $this->assertEquals($expected, $period->withDurationBeforeEnd('1 MONTH'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testWithDurationBeforeEndThrowsException()
    {
        $period = Period::createFromDurationBeforeEnd('2014-03-01', '2 Weeks');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDurationBeforeEnd($interval);
    }

    public function testMerge()
    {
        $period = Period::createFromMonth(2014, 3);
        $altPeriod = Period::createFromMonth(2014, 4);
        $expected = Period::createFromDuration('2014-03-01', '2 MONTHS');
        $this->assertEquals($expected, $period->merge($altPeriod));
        $this->assertEquals($expected, $altPeriod->merge($period));
        $this->assertEquals($expected, $expected->merge($period, $altPeriod));
    }

    public function testAdd()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $period = $orig->add('1 MONTH');
        $this->assertTrue($period->durationGreaterThan($orig));
        $this->assertEquals($orig->getStartDate(), $period->getStartDate());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddThrowsLogicException()
    {
        Period::createFromDuration('2012-01-01', '1 MONTH')->add('-3 MONTHS');
    }

    public function testMoveStartDateBackward()
    {
        $orig = Period::createFromMonth(2012, 1);
        $period = $orig->moveStartDate('-1 MONTH');
        $this->assertTrue($period->durationGreaterThan($orig));
        $this->assertEquals($orig->getEndDate(), $period->getEndDate());
        $this->assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveStartDateForward()
    {
        $orig = Period::createFromMonth(2012, 1);
        $period = $orig->moveStartDate('2 WEEKS');
        $this->assertTrue($period->durationLessThan($orig));
        $this->assertEquals($orig->getEndDate(), $period->getEndDate());
        $this->assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    /**
     * @expectedException \LogicException
     */
    public function testMoveStartDateThrowsLogicException()
    {
        Period::createFromDuration('2012-01-01', '1 MONTH')->moveStartDate('3 MONTHS');
    }

    public function testSub()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
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
        $this->assertEquals($next->getStartDate(), $orig->getEndDate());
    }

    public function testNextWithoutDuration()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertEquals($orig->next()->getStartDate(), $orig->getEndDate());
    }

    public function testPrevious()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertEquals($orig->previous('1 MONTH')->getEndDate(), $orig->getStartDate());
    }

    public function testPreviousWithoutDuration()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $this->assertEquals($orig->previous()->getEndDate(), $orig->getStartDate());
    }

    public function testPreviousNext()
    {
        $period = Period::createFromWeek(2014, 13);
        $this->assertTrue($period->sameValueAs($period->next('3 MONTH')->previous('1 WEEK')));
    }

    public function testDateIntervalDiff()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $this->assertInstanceof(DateInterval::class, $orig->dateIntervalDiff($alt));
    }

    public function testTimeIntervalDiff()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $this->assertSame(-3600, $orig->timestampIntervalDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        $this->assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }

    public function testIntersect()
    {
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');

        $this->assertInstanceof(Period::class, $orig->intersect($alt));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange()
    {
        $orig = Period::createFromDuration('2013-01-01', '1 MONTH');
        $orig->intersect(Period::createFromDuration('2012-01-01', '2 MONTH'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIntersectThrowsExceptionWithAdjacentTimeRange()
    {
        $orig = Period::createFromDuration('2013-01-01', '1 MONTH');
        $orig->intersect($orig->next());
    }

    public function testGap()
    {
        $orig = Period::createFromDuration('2011-12-01', '2 MONTHS');
        $alt = Period::createFromDuration('2012-06-15', '3 MONTHS');
        $res = $orig->gap($alt);
        $this->assertInstanceof(Period::class, $res);
        $this->assertEquals($orig->getEndDate(), $res->getStartDate());
        $this->assertEquals($alt->getStartDate(), $res->getEndDate());
        $this->assertTrue($res->sameValueAs($alt->gap($orig)));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapThrowsExceptionWithOverlapsPeriod()
    {
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $orig->gap(Period::createFromDuration('2012-01-01', '2 MONTH'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapWithSameStartingPeriod()
    {
        $orig = Period::createFromDuration('2012-12-01', '5 MONTH');
        $orig->gap(Period::createFromDuration('2012-12-01', '2 MONTH'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGapWithSameEndingPeriod()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $orig->gap(Period::createFromDurationBeforeEnd('2012-12-01', '2 MONTH'));
    }

    public function testGapWithAdjacentPeriod()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $alt  = $orig->next('1 MINUTE');
        $res  = $orig->gap($alt);
        $this->assertInstanceof(Period::class, $res);
        $this->assertSame(0, $res->getTimestampInterval());
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
        $this->assertInstanceof(Period::class, $res[0]);
        $this->assertEquals(new DateTimeImmutable('2013-04-01'), $res[0]->getStartDate());
        $this->assertEquals(new DateTimeImmutable('2014-01-01'), $res[0]->getEndDate());
    }

    public function testDiffWithOverlapsPeriod()
    {
        $period = Period::createFromDuration('2013-01-01 10:00:00', '3 HOURS');
        $alt = Period::createFromDuration('2013-01-01 11:00:00', '3 HOURS');
        $res = $alt->diff($period);
        $this->assertCount(2, $res);
        $this->assertEquals(3600, $res[1]->getTimestampInterval());
        $this->assertEquals(3600, $res[0]->getTimestampInterval());
    }

    public function testMove()
    {
        $period = new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01');
        $moved = $period->move(new DateInterval('P1D'));
        $this->assertEquals(new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01'), $moved);
    }

    public function testMoveSupportStringIntervals()
    {
        $period = new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01');
        $advanced = $period->move('1 DAY');
        $this->assertEquals(new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01'), $advanced);
    }

    public function testMoveWithInvertedInterval()
    {
        $period = new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01');
        $lessOneDay = new DateInterval('P1D');
        $lessOneDay->invert = true;
        $moved = $period->move($lessOneDay);
        $this->assertEquals(new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01'), $moved);
    }

    public function testMoveWithInvertedStringInterval()
    {
        $period = new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01');
        $moved = $period->move('- 1 day');
        $this->assertEquals(new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01'), $moved);
    }
}
