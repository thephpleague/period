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
     * Period starting included datepoint.
     *
     * @var \DateTimeInterface|\DateTime
     */
    private $startDate;

    /**
     * Period ending excluded datepoint.
     *
     * @var \DateTimeInterface|\DateTime
     */
    private $endDate;

    /**
     * Create a new instance.
     *
     * @param string|\DateTimeInterface|\DateTime $startDate starting datepoint
     * @param string|\DateTimeInterface|\DateTime $endDate   ending datepoint
     *
     * @throws \LogicException If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = self::validateDateTime($startDate);
        $endDate   = self::validateDateTime($endDate);
        if ($startDate > $endDate) {
            throw new LogicException('the ending endpoint must be greater or equal to the starting endpoint');
        }
        $this->startDate = clone $startDate;
        $this->endDate   = clone $endDate;
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
        $startDate = clone $this->startDate;
        $endDate   = clone $this->endDate;

        return $startDate->setTimeZone($utc)->format(self::ISO8601).'/'.$endDate->setTimeZone($utc)->format(self::ISO8601);
    }

    /**
     * Returns the starting datepoint.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getStartDate()
    {
        return clone $this->startDate;
    }

    /**
     * Returns the ending endpoint.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getEndDate()
    {
        return clone $this->endDate;
    }

    /**
     * Returns the Period duration as expressed in seconds
     *
     * @return double
     */
    public function getTimestampInterval()
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * Returns the Period duration as a DateInterval object.
     *
     * @return \DateInterval
     */
    public function getDateInterval()
    {
        return $this->startDate->diff($this->endDate);
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
        return new DatePeriod($this->startDate, self::validateDateInterval($interval), $this->endDate);
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
     *
     * @return \DateInterval
     */
    private static function validateDateInterval($interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        } elseif (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        }

        return DateInterval::createFromDateString($interval);
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
        return $this->startDate == $period->startDate && $this->endDate == $period->endDate;
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
        return $this->startDate == $period->endDate || $this->endDate == $period->startDate;
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

        return $this->startDate < $period->endDate && $this->endDate > $period->startDate;
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
            return $this->startDate >= $index->endDate;
        }

        return $this->startDate > self::validateDateTime($index);
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
            return $this->endDate <= $index->startDate;
        }

        return $this->endDate <= self::validateDateTime($index);
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
            return $this->contains($index->startDate) && $this->contains($index->endDate);
        }

        $datetime = self::validateDateTime($index);

        return $datetime >= $this->startDate && $datetime < $this->endDate;
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
        $datetime = clone $this->startDate;
        $datetime->add($period->getDateInterval());
        if ($this->endDate > $datetime) {
            return 1;
        } elseif ($this->endDate < $datetime) {
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
     * @param string|\DateTimeInterface|\DateTime $startDate start datepoint
     * @param \DateInterval|int|string            $interval  The duration. If an int is passed, it is
     *                                                       interpreted as the duration expressed in seconds.
     *                                                       If a string is passed, it must be parsable by
     *                                                       `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDuration($startDate, $interval)
    {
        $startDate = self::validateDateTime($startDate);
        $endDate   = clone $startDate;

        return new self($startDate, $endDate->add(self::validateDateInterval($interval)));
    }

    /**
     * Create a Period object from a ending endpoint and an interval.
     *
     * @param string|\DateTimeInterface|\DateTime $endDate  end datepoint
     * @param \DateInterval|int|string            $interval The duration. If an int is passed, it is
     *                                                      interpreted as the duration expressed in seconds.
     *                                                      If a string is passed, it must be parsable by
     *                                                      `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDurationBeforeEnd($endDate, $interval)
    {
        $endDate   = self::validateDateTime($endDate);
        $startDate = clone $endDate;

        return new self($startDate->sub(self::validateDateInterval($interval)), $endDate);
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
     * Returns a new Period object with a new included starting datepoint.
     *
     * @param string|\DateTimeInterface|\DateTime $startDate datepoint
     *
     * @throws \LogicException If $startDate does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function startingOn($startDate)
    {
        return new self(self::validateDateTime($startDate), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending datepoint.
     *
     * @param string|\DateTimeInterface|\DateTime $endDate datepoint
     *
     * @throws \LogicException If $endDate does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function endingOn($endDate)
    {
        return new self($this->startDate, self::validateDateTime($endDate));
    }

    /**
     * Returns a new Period object with a new ending datepoint.
     *
     * @param \DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public function withDuration($interval)
    {
        return self::createFromDuration($this->startDate, $interval);
    }

    /**
     * Returns a new Period object with an added interval
     *
     * @param \DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $interval does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function add($interval)
    {
        $endDate = clone $this->endDate;

        return new self($this->startDate, $endDate->add(self::validateDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a Removed interval
     *
     * @param \DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $interval does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function sub($interval)
    {
        $endDate = clone $this->endDate;

        return new self($this->startDate, $endDate->sub(self::validateDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and starting with its ending endpoint.
     * If no duration is provided the new Period will be created
     * using the current object duration
     *
     * @param \DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     * @return \League\Period\Period
     */
    public function next($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return self::createFromDuration($this->endDate, $interval);
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and ending with its starting endpoint.
     * If no duration is provided the new Period will have the
     * same duration as the current one
     *
     * @param \DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     * @return \League\Period\Period
     */
    public function previous($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return self::createFromDurationBeforeEnd($this->startDate, $interval);
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
        $res = clone $this;
        array_walk($args, function (Period $period) use (&$res) {
            if ($res->startDate > $period->startDate) {
                $res = $res->startingOn($period->startDate);
            }
            if ($res->endDate < $period->endDate) {
                $res = $res->endingOn($period->endDate);
            }
        });

        return $res;
    }

    /**
     * Split a Period by a given interval
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must bep arsable by
     *                                           `DateInterval::createFromDateString`
     * @return \League\Period\Period[]
     */
    public function split($interval)
    {
        $res = [];
        foreach ($this->getDatePeriod($interval) as $startDate) {
            $period = self::createFromDuration($startDate, $interval);
            if ($period->contains($this->endDate)) {
                $period = $period->endingOn($this->endDate);
            }
            $res[] = $period;
        }

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
            ($period->startDate > $this->startDate) ? $period->startDate : $this->startDate,
            ($period->endDate < $this->endDate) ? $period->endDate : $this->endDate
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
        if ($period->startDate > $this->startDate) {
            return new self($this->endDate, $period->startDate);
        }

        return new self($period->endDate, $this->startDate);
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
            self::createFromEndpoints($this->startDate, $period->startDate),
            self::createFromEndpoints($this->endDate, $period->endDate),
        ];

        return array_values(array_filter($res, function (Period $period) {
            return $period->startDate != $period->endDate;
        }));
    }

    /**
     * Create a new Period instance given two endpoints
     * The endpoints will be used as to allow the creation of
     * a Period object
     *
     * @param string|\DateTimeInterface|\DateTime $endPoint1 endpoint
     * @param string|\DateTimeInterface|\DateTime $endPoint2 endpoint
     *
     * @return \League\Period\Period
     */
    private static function createFromEndpoints($endPoint1, $endPoint2)
    {
        $startDate = self::validateDateTime($endPoint1);
        $endDate   = self::validateDateTime($endPoint2);
        if ($startDate > $endDate) {
            return new self($endDate, $startDate);
        }

        return new self($startDate, $endDate);
    }

    /**
     * Returns the difference between two Period objects expressed in seconds
     *
     * @param \League\Period\Period $period
     *
     * @return double
     */
    public function timestampIntervalDiff(Period $period)
    {
        return $this->getTimestampInterval() - $period->getTimestampInterval();
    }

    /**
     * Returns the difference between two Period objects expressed in \DateInterval
     *
     * @param \League\Period\Period $period
     *
     * @return \DateInterval
     */
    public function dateIntervalDiff(Period $period)
    {
        return $this->endDate->diff($this->withDuration($period->getDateInterval())->endDate);
    }

    /**
     * Returns the starting DateTime.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     * @codeCoverageIgnore
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getStart()
    {
        return $this->getStartDate();
    }

    /**
     * Returns the ending DateTime.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     * @codeCoverageIgnore
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getEnd()
    {
        return $this->getEndDate();
    }

    /**
     * Returns the Period duration as a DateInterval object.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     * @codeCoverageIgnore
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
            return $this->getTimestampInterval();
        }

        return $this->getDateInterval();
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the Period object.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     * @codeCoverageIgnore
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
     * Returns the difference between two Period objects.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 2.5
     * @codeCoverageIgnore
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
            return $this->timestampIntervalDiff($period);
        }

        return $this->dateIntervalDiff($period);
    }
}
