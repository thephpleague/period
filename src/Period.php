<?php

/**
 * This file is part of the Period library.
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/period/
 * @version 2.4.0
 * @package League.Period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;

/**
 * A value object class to manipulate Time Range.
 */
final class Period implements PeriodInterface
{
    /**
     * DateTime Format to create ISO8601 Interval format
     */
    const ISO8601 = 'Y-m-d\TH:i:s\Z';

    /**
     * Period starting included datetime endpoint.
     *
     * @var \DateTimeInterface|\DateTime
     */
    private $start;

    /**
     * Period ending excluded datetime endpoint.
     *
     * @var \DateTimeInterface|\DateTime
     */
    private $end;

    /**
     * Create a new instance.
     *
     * <code>
     * <?php
     *  $period = new Period('2012-01-01', '2012-02-17');
     * ?>
     * </code>
     *
     * @param string|\DateTimeInterface|\DateTime $start starting datetime endpoint
     * @param string|\DateTimeInterface|\DateTime $end   ending datetime endpoint
     *
     * @throws \LogicException If $start is greater than $end
     *
     * @return void
     */
    public function __construct($start, $end)
    {
        $start = self::validateDateTime($start);
        $end   = self::validateDateTime($end);
        if ($start > $end) {
            throw new LogicException('the ending endpoint must be greater or equal to the starting endpoint');
        }
        $this->start = clone $start;
        $this->end   = clone $end;
    }

    /**
     * @inheritdoc
     */
    private static function validateDateTime($datetime)
    {
        if ($datetime instanceof DateTimeInterface || $datetime instanceof DateTime) {
            return $datetime;
        }

        return new DateTime((string) $datetime);
    }

    /**
     * @inheritdoc
     */
    public function getStart()
    {
        return clone $this->start;
    }

    /**
     * @inheritdoc
     */
    public function getEnd()
    {
        return clone $this->end;
    }

    /**
     * @inheritdoc
     */
    public function getDuration($get_as_seconds = false)
    {
        if ($get_as_seconds) {
            return $this->end->getTimestamp() - $this->start->getTimestamp();
        }

        return $this->start->diff($this->end);
    }

    /**
     * @inheritdoc
     */
    public function getRange($interval)
    {
        return new DatePeriod($this->start, self::validateDateInterval($interval), $this->end);
    }

    /**
     * @inheritdoc
     */
    private static function validateDateInterval($interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        } elseif (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        }

        return DateInterval::createFromDateString((string) $interval);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        $utc   = new DateTimeZone('UTC');
        $start = clone $this->start;
        $end   = clone $this->end;

        return $start->setTimeZone($utc)->format(self::ISO8601).'/'.$end->setTimeZone($utc)->format(self::ISO8601);
    }

    /**
     * @inheritdoc
     */
    public function sameValueAs(PeriodInterface $period)
    {
        return $this->start == $period->start && $this->end == $period->end;
    }

    /**
     * @inheritdoc
     */
    public function abuts(PeriodInterface $period)
    {
        return $this->start == $period->end || $this->end == $period->start;
    }

    /**
     * @inheritdoc
     */
    public function overlaps(PeriodInterface $period)
    {
        if ($this->abuts($period)) {
            return false;
        }

        return $this->start < $period->end && $this->end > $period->start;
    }

    /**
     * @inheritdoc
     */
    public function isAfter($index)
    {
        if ($index instanceof Period) {
            return $this->start >= $index->end;
        }

        return $this->start > self::validateDateTime($index);
    }

    /**
     * @inheritdoc
     */
    public function isBefore($index)
    {
        if ($index instanceof Period) {
            return $this->end <= $index->start;
        }

        return $this->end <= self::validateDateTime($index);
    }

    /**
     * @inheritdoc
     */
    public function contains($index)
    {
        if ($index instanceof Period) {
            return $this->contains($index->start) && $this->contains($index->end);
        }

        $datetime = self::validateDateTime($index);

        return $datetime >= $this->start && $datetime < $this->end;
    }

    /**
     * @inheritdoc
     */
    public function diff(PeriodInterface $period)
    {
        if (! $this->overlaps($period)) {
            throw new LogicException('Both Period objects should overlaps');
        }

        $res = array(
            self::createFromEndpoints($this->start, $period->start),
            self::createFromEndpoints($this->end, $period->end),
        );

        return array_values(array_filter($res, function (Period $period) {
            return $period->getStart() != $period->getEnd();
        }));
    }

    /**
     * Create a new PeriodInterface instance given two endpoints
     * The endpoints will be used as to allow the creation of
     * a Period object
     *
     * @param string|\DateTimeInterface|\DateTime $endpoint1 endpoint
     * @param string|\DateTimeInterface|\DateTime $endpoint2 endpoint
     *
     * @return \League\Period\PeriodInterface
     */
    private static function createFromEndpoints($endpoint1, $endpoint2)
    {
        $start = self::validateDateTime($endpoint1);
        $end   = self::validateDateTime($endpoint2);
        if ($start > $end) {
            return new self($end, $start);
        }

        return new self($start, $end);
    }

    /**
     * @inheritdoc
     */
    public function durationDiff(PeriodInterface $period, $get_as_seconds = false)
    {
        if ($get_as_seconds) {
            return $this->end->getTimestamp()
                - $this->start->getTimestamp()
                - $period->end->getTimestamp()
                + $period->start->getTimestamp();
        }
        $normPeriod = $this->withDuration($period->start->diff($period->end));

        return $this->end->diff($normPeriod->end);
    }

    /**
     * @inheritdoc
     */
    public function compareDuration(PeriodInterface $period)
    {
        $datetime = clone $this->start;
        $datetime->add($period->start->diff($period->end));
        if ($this->end > $datetime) {
            return 1;
        } elseif ($this->end < $datetime) {
            return -1;
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function durationGreaterThan(PeriodInterface $period)
    {
        return 1 === $this->compareDuration($period);
    }

    /**
     * @inheritdoc
     */
    public function durationLessThan(PeriodInterface $period)
    {
        return -1 === $this->compareDuration($period);
    }

    /**
     * @inheritdoc
     */
    public function sameDurationAs(PeriodInterface $period)
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * Create a Period object from a starting point and an interval.
     *
     * <code>
     *<?php
     * $period = Period::createFromDuration('2012-01-01', '1 HOUR');
     * $period = Period::createFromDuration(new DateTime('2012-01-01'), new DateInterval('PT1H'));
     * $period = Period::createFromDuration(new DateTime('2012-01-01'), '1 HOUR');
     * $period = Period::createFromDuration('2012-01-01', new DateInterval('PT1H'));
     * $period = Period::createFromDuration('2012-01-01', 3600);
     *
     * ?>
     * </code>
     *
     * @param string|\DateTimeInterface|\DateTime $start    start datetime endpoint
     * @param \DateInterval|int|string            $duration The duration. If an int is passed, it is
     *                                                      interpreted as the duration expressed in seconds.
     *                                                      If a string is passed, it must be parsable by
     *                                                      `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDuration($start, $duration)
    {
        $start = self::validateDateTime($start);
        $end   = clone $start;

        return new self($start, $end->add(self::validateDateInterval($duration)));
    }

    /**
     * Create a Period object from a ending endpoint and an interval.
     *
     * <code>
     *<?php
     * $period = Period::createFromDurationBeforeEnd('2012-01-01', '1 HOUR');
     * $period = Period::createFromDurationBeforeEnd(new DateTime('2012-01-01'), new DateInterval('PT1H'));
     * $period = Period::createFromDurationBeforeEnd(new DateTime('2012-01-01'), '1 HOUR');
     * $period = Period::createFromDurationBeforeEnd('2012-01-01', new DateInterval('PT1H'));
     * $period = Period::createFromDurationBeforeEnd('2012-01-01', 3600);
     *
     * ?>
     * </code>
     *
     * @param string|\DateTimeInterface|\DateTime $end      end datetime endpoint
     * @param \DateInterval|int|string            $duration The duration. If an int is passed, it is
     *                                                      interpreted as the duration expressed in seconds.
     *                                                      If a string is passed, it must be parsable by
     *                                                      `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDurationBeforeEnd($end, $duration)
    {
        $end   = self::validateDateTime($end);
        $start = clone $end;

        return new self($start->sub(self::validateDateInterval($duration)), $end);
    }

    /**
     * Create a Period object from a Year and a Week.
     *
     * <code>
     *<?php
     * $period = Period::createFromWeek(2012, 3);
     *
     * ?>
     * </code>
     *
     * @param int $year
     * @param int $week index from 1 to 53
     *
     * @return \League\Period\Period
     */
    public static function createFromWeek($year, $week)
    {
        return self::createFromDuration(
            self::validateYear($year).'W'.sprintf('%02d', self::validateRange($week, 1, 53)),
            '1 WEEK'
        );
    }

    /**
     * Validate a year.
     *
     * @param int $year
     *
     * @throws \InvalidArgumentException If year is not a valid int
     *
     * @return int
     */
    private static function validateYear($year)
    {
        $year = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $year) {
            throw new InvalidArgumentException("A Year must be a valid int");
        }

        return $year;
    }

    /**
     * Validate a int according to a range.
     *
     * @param int $value the value to validate
     * @param int $min   the minimun value
     * @param int $max   the maximal value
     *
     * @return int
     *
     * @throws \OutOfRangeException If the value is not in the range
     */
    private static function validateRange($value, $min, $max)
    {
        $res = filter_var($value, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => $min, 'max_range' => $max)
        ));
        if (false === $res) {
            throw new OutOfRangeException("the submitted value is not contained within the valid range");
        }

        return $res;
    }

    /**
     * Create a Period object from a Year and a Month.
     *
     * <code>
     *<?php
     * $period = Period::createFromMonth(2012, 11);
     *
     * ?>
     * </code>
     *
     * @param int $year
     * @param int $month Month index from 1 to 12
     *
     * @return \League\Period\Period
     */
    public static function createFromMonth($year, $month)
    {
        $year  = self::validateYear($year);
        $month = self::validateRange($month, 1, 12);

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '1 MONTH');
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * <code>
     *<?php
     * $period = Period::createFromQuarter(2012, 2);
     *
     * ?>
     * </code>
     *
     * @param int $year
     * @param int $quarter Quarter Index from 1 to 4
     *
     * @return \League\Period\Period
     */
    public static function createFromQuarter($year, $quarter)
    {
        $year  = self::validateYear($year);
        $month = ((self::validateRange($quarter, 1, 4) - 1) * 3) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '3 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * <code>
     *<?php
     * $period = Period::createFromBiennal(2012, 1);
     *
     * ?>
     * </code>
     *
     * @param int $year
     * @param int $semester Semester Index from 1 to 2
     *
     * @return \League\Period\Period
     */
    public static function createFromSemester($year, $semester)
    {
        $year  = self::validateYear($year);
        $month = ((self::validateRange($semester, 1, 2) - 1) * 6) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '6 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * <code>
     *<?php
     * $period = Period::createFromYear(2012);
     *
     * ?>
     * </code>
     *
     * @param int $year
     *
     * @return \League\Period\Period
     */
    public static function createFromYear($year)
    {
        return self::createFromDuration(self::validateYear($year).'-01-01', '1 YEAR');
    }

    /**
     * @inheritdoc
     */
    public function startingOn($start)
    {
        return new self(self::validateDateTime($start), $this->end);
    }

    /**
     * @inheritdoc
     */
    public function endingOn($end)
    {
        return new self($this->start, self::validateDateTime($end));
    }

    /**
     * @inheritdoc
     */
    public function withDuration($duration)
    {
        return self::createFromDuration($this->start, $duration);
    }

    /**
     * @inheritdoc
     */
    public function add($duration)
    {
        $end = clone $this->end;

        return new self($this->start, $end->add(self::validateDateInterval($duration)));
    }

    /**
     * @inheritdoc
     */
    public function sub($duration)
    {
        $end = clone $this->end;

        return new self($this->start, $end->sub(self::validateDateInterval($duration)));
    }

    /**
     * @inheritdoc
     */
    public function next($duration = null)
    {
        if (is_null($duration)) {
            $duration = $this->getDuration();
        }

        return self::createFromDuration($this->end, $duration);
    }

    /**
     * @inheritdoc
     */
    public function previous($duration = null)
    {
        if (is_null($duration)) {
            $duration = $this->getDuration();
        }

        return self::createFromDurationBeforeEnd($this->start, $duration);
    }

    /**
     * @inheritdoc
     */
    public function merge()
    {
        $res = clone $this;
        $args = func_get_args();
        array_walk($args, function (Period $period) use (&$res) {
            $start = $period->getStart();
            if ($res->getStart() > $start) {
                $res = $res->startingOn($start);
            }
            $end = $period->getEnd();
            if ($res->getEnd() < $end) {
                $res = $res->endingOn($end);
            }
        });

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function intersect(PeriodInterface $period)
    {
        if (! $this->overlaps($period)) {
            throw new LogicException('Both Period objects should overlaps');
        }

        $start = $this->start;
        if ($period->start > $start) {
            $start = $period->start;
        }

        $end = $this->end;
        if ($period->end < $end) {
            $end = $period->end;
        }

        return new self($start, $end);
    }

    /**
     * @inheritdoc
     */
    public function gap(PeriodInterface $period)
    {
        if ($this->overlaps($period)) {
            throw new LogicException('Both Period objects should not overlaps');
        }

        if ($period->start > $this->start) {
            return new self($this->end, $period->start);
        }

        return new self($period->end, $this->start);
    }
}
