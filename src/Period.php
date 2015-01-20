<?php

/**
 * This file is part of the Period library.
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/period/
 * @version 2.5.0
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
use RuntimeException;

/**
 * A immutable value object class to manipulate Time Range.
 */
final class Period
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
     * Validate a DateTime.
     *
     * @param string|\DateTimeInterface|\DateTime $datetime
     *
     * @throws \RuntimeException If The Data can not be converted into a proper DateTime object
     *
     * @return \DateTimeInterface|\DateTime
     */
    private static function validateDateTime($datetime)
    {
        if ($datetime instanceof DateTimeInterface || $datetime instanceof DateTime) {
            return $datetime;
        }

        return new DateTime($datetime);
    }

    /**
     * String representation of a Period using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString()
    {
        $utc   = new DateTimeZone('UTC');
        $start = clone $this->start;
        $end   = clone $this->end;

        return $start->setTimeZone($utc)->format(self::ISO8601).'/'.$end->setTimeZone($utc)->format(self::ISO8601);
    }

    /**
     * Returns the starting DateTime.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getStart()
    {
        return clone $this->start;
    }

    /**
     * Returns the ending DateTime.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getEnd()
    {
        return clone $this->end;
    }

    /**
     * Returns the Period duration as a DateInterval object.
     *
     * @param bool $get_as_seconds If used and set to true, the method will return an int which
     *                             represents the duration in seconds instead of a \DateInterval
     *                             object.
     *
     * @return \DateInterval|int|double
     */
    public function getDuration($get_as_seconds = false)
    {
        if ($get_as_seconds) {
            return $this->end->getTimestamp() - $this->start->getTimestamp();
        }

        return $this->start->diff($this->end);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the Period object.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \DatePeriod
     */
    public function getRange($interval)
    {
        return $this->getDatePeriod($interval);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the Period object.
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \DatePeriod
     */
    public function getDatePeriod($interval)
    {
        return new DatePeriod($this->start, self::validateDateInterval($interval), $this->end);
    }

    /**
     * Validate a DateInterval.
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must bep arsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \Exception If the integer generates a bad format
     * @throws \RuntimException If the string can not be converted into a proper DateInterval object
     *
     * @return \DateInterval
     */
    private static function validateDateInterval($interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        } elseif (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        } elseif (false === ($res = @DateInterval::createFromDateString($interval))) {
            throw new RuntimeException('The given $interval could not be converted into a DateInterval');
        }

        return $res;
    }

    /**
     * Tells whether two Period share the same endpoints.
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function sameValueAs(Period $period)
    {
        return $this->start == $period->start && $this->end == $period->end;
    }

    /**
     * Tells whether two Period object abuts
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function abuts(Period $period)
    {
        return $this->start == $period->end || $this->end == $period->start;
    }

    /**
     * Tells whether two Period objects overlaps.
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function overlaps(Period $period)
    {
        if ($this->abuts($period)) {
            return false;
        }

        return $this->start < $period->end && $this->end > $period->start;
    }

    /**
     * Tells whether a Period is entirely after the specified index
     *
     * @param \League\Period\Period|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isAfter($index)
    {
        if ($index instanceof Period) {
            return $this->start >= $index->end;
        }

        return $this->start > self::validateDateTime($index);
    }

    /**
     * Tells whether a Period is entirely before the specified index
     *
     * @param \League\Period\Period|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isBefore($index)
    {
        if ($index instanceof Period) {
            return $this->end <= $index->start;
        }

        return $this->end <= self::validateDateTime($index);
    }

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param \League\Period\Period|\DateTimeInterface|\DateTime $index
     *
     * @return bool
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
     * Compares two Period objects according to their duration.
     *
     * @param \League\Period\Period $period
     *
     * @return int
     */
    public function compareDuration(Period $period)
    {
        $datetime = clone $this->start;
        $datetime->add($period->getDuration());
        if ($this->end > $datetime) {
            return 1;
        } elseif ($this->end < $datetime) {
            return -1;
        }

        return 0;
    }

    /**
     * Tells whether the current Period object duration
     * is greater than the submitted one.
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function durationGreaterThan(Period $period)
    {
        return 1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is less than the submitted one.
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function durationLessThan(Period $period)
    {
        return -1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is equal to the submitted one
     *
     * @param \League\Period\Period $period
     *
     * @return bool
     */
    public function sameDurationAs(Period $period)
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * Create a Period object from a starting point and an interval.
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
        $res = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if (false === $res) {
            throw new OutOfRangeException("the submitted value is not contained within the valid range");
        }

        return $res;
    }

    /**
     * Create a Period object from a Year and a Month.
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
     * @param int $year
     *
     * @return \League\Period\Period
     */
    public static function createFromYear($year)
    {
        return self::createFromDuration(self::validateYear($year).'-01-01', '1 YEAR');
    }

    /**
     * Returns a new Period object with a new includedd starting endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $start starting included datetime endpoint
     *
     * @throws \LogicException If $start does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function startingOn($start)
    {
        return new self(self::validateDateTime($start), $this->end);
    }

    /**
     * Returns a new Period object with a new excluded ending endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $end ending excluded datetime endpoint
     *
     * @throws \LogicException If $end does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function endingOn($end)
    {
        return new self($this->start, self::validateDateTime($end));
    }

    /**
     * Returns a new Period object with a new excluded ending endpoint.
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public function withDuration($duration)
    {
        return self::createFromDuration($this->start, $duration);
    }

    /**
     * Returns a new Period object with an added interval
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $duration does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function add($duration)
    {
        $end = clone $this->end;

        return new self($this->start, $end->add(self::validateDateInterval($duration)));
    }

    /**
     * Returns a new Period object with a Removed interval
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $duration does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function sub($duration)
    {
        $end = clone $this->end;

        return new self($this->start, $end->sub(self::validateDateInterval($duration)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and starting with its ending endpoint.
     * If no duration is provided the new Period will be created
     * using the current object duration
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\Period
     */
    public function next($duration = null)
    {
        if (is_null($duration)) {
            $duration = $this->getDuration();
        }

        return self::createFromDuration($this->end, $duration);
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and ending with its starting endpoint.
     * If no duration is provided the new Period will have the
     * same duration as the current one
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\Period
     */
    public function previous($duration = null)
    {
        if (is_null($duration)) {
            $duration = $this->getDuration();
        }

        return self::createFromDurationBeforeEnd($this->start, $duration);
    }

    /**
     * Merges one or more Period objects to return a new Period object.
     *
     * The resultant object englobes the largest duration possible.
     *
     * @param \League\Period\Period $arg,... one or more Period objects
     *
     * @return \League\Period\Period
     */
    public function merge()
    {
        $args = func_get_args();
        if (! $args) {
            throw new RuntimeException(__METHOD__.' is expecting at least one argument');
        }
        $res  = clone $this;
        array_walk($args, function (Period $period) use (&$res) {
            if ($res->start > $period->start) {
                $res = $res->startingOn($period->start);
            }
            if ($res->end < $period->end) {
                $res = $res->endingOn($period->end);
            }
        });

        return $res;
    }

    /**
     * Computes the intersection between two Period objects.
     *
     * @param \League\Period\Period $period
     *
     * @throws \LogicException If Both objects do not overlaps
     *
     * @return \League\Period\Period
     */
    public function intersect(Period $period)
    {
        if ($this->abuts($period)) {
            throw new LogicException('Both object should not abuts');
        }

        return new self(
            ($period->start > $this->start) ? $period->start : $this->start,
            ($period->end < $this->end) ? $period->end : $this->end
        );
    }

    /**
     * Computes the gap between two Period objects.
     *
     * @param \League\Period\Period $period
     *
     * @throws \LogicException If Both objects overlaps
     *
     * @return \League\Period\Period
     */
    public function gap(Period $period)
    {
        if ($period->start > $this->start) {
            return new self($this->end, $period->start);
        }

        return new self($period->end, $this->start);
    }

    /**
     * Computes the difference between two overlapsing Period objects
     * and return an array containing the difference expressed as Period objects
     * The array will:
     * - be empty if both objects have the same endpoints
     * - contain one Period object if both objects share one endpoint
     * - contain two Period objects if both objects share no endpoint
     *
     * @param \League\Period\Period $period
     *
     * @throws \LogicException if both object do not overlaps
     *
     * @return \League\Period\Period[]
     */
    public function diff(Period $period)
    {
        if (! $this->overlaps($period)) {
            throw new LogicException('Both Period objects should overlaps');
        }

        $res = [
            self::createFromEndpoints($this->start, $period->start),
            self::createFromEndpoints($this->end, $period->end),
        ];

        return array_values(array_filter($res, function (Period $period) {
            return $period->start != $period->end;
        }));
    }

    /**
     * Create a new Period instance given two endpoints
     * The endpoints will be used as to allow the creation of
     * a Period object
     *
     * @param string|\DateTimeInterface|\DateTime $endpoint1 endpoint
     * @param string|\DateTimeInterface|\DateTime $endpoint2 endpoint
     *
     * @return \League\Period\Period
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
     * Returns the difference between two Period objects.
     *
     * @param \League\Period\Period $period
     * @param bool                  $get_as_seconds If used and set to true, the method will return
     *                                              an int which represents the duration in seconds
     *                                              instead of a\DateInterval object
     *
     * @return \DateInterval|int|double
     */
    public function durationDiff(Period $period, $get_as_seconds = false)
    {
        if ($get_as_seconds) {
            return $this->getDuration(true) - $period->getDuration(true);
        }

        return $this->end->diff($this->withDuration($period->getDuration())->end);
    }
}
