<?php

/**
 * This file is part of the Period library.
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/period/
 * @version 3.0.0
 * @package League.Period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use OutOfRangeException;
use RuntimeException;

/**
 * A immutable value object class to manipulate Time Range.
 */
final class Period implements JsonSerializable
{
    /**
     * Date Format to create ISO8601 Interval format
     */
    const ISO8601 = 'Y-m-d\TH:i:s\Z';

    /**
     * Period starting included datepoint.
     *
     * @var \DateTimeImmutable
     */
    private $startDate;

    /**
     * Period ending excluded datepoint.
     *
     * @var \DateTimeImmutable
     */
    private $endDate;

    /**
     * Create a new instance.
     *
     * @param string|\DateTimeInterface $startDate starting datepoint
     * @param string|\DateTimeInterface $endDate   ending datepoint
     *
     * @throws \LogicException If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $this->startDate = self::validateDatePoint($startDate);
        $this->endDate   = self::validateDatePoint($endDate);
        if ($this->startDate > $this->endDate) {
            throw new LogicException('the ending datepoint must be greater or equal to the starting datepoint');
        }
    }

    /**
     * Validate a DateTimeImmutable object.
     *
     * @param string|\DateTimeInterface $datetime
     *
     * @throws \RuntimeException If The Data can not be converted into a proper DateTimeImmutable object
     *
     * @return \DateTimeImmutable
     */
    private static function validateDatePoint($datetime)
    {
        if ($datetime instanceof DateTimeImmutable) {
            return $datetime;
        } elseif ($datetime instanceof DateTime) {
            return new DateTimeImmutable($datetime->format('c'), $datetime->getTimeZone());
        }

        return new DateTimeImmutable($datetime);
    }

    /**
     * Create a Period object from a starting point and an interval.
     *
     * @param string|\DateTimeInterface $startDate start datepoint
     * @param \DateInterval|int|string  $interval  The duration. If an int is passed, it is
     *                                             interpreted as the duration expressed in seconds.
     *                                             If a string is passed, it must be parsable by
     *                                             `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDuration($startDate, $interval)
    {
        $date = self::validateDatePoint($startDate);

        return new self($date, $date->add(self::validateDateInterval($interval)));
    }

    /**
     * Validate a DateInterval.
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
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
     * Create a Period object from a ending datepoint and an interval.
     *
     * @param string|\DateTimeInterface $endDate  end datepoint
     * @param \DateInterval|int|string  $interval The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     *
     * @return \League\Period\Period
     */
    public static function createFromDurationBeforeEnd($endDate, $interval)
    {
        $date = self::validateDatePoint($endDate);

        return new self($date->sub(self::validateDateInterval($interval)), $date);
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
        $startDate = new DateTimeImmutable(
            self::validateYear($year).'W'.sprintf('%02d', self::validateRange($week, 1, 53))
        );

        return new self($startDate, $startDate->add(new DateInterval('P1W')));
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
        $month = sprintf('%02s', self::validateRange($month, 1, 12));
        $startDate = new DateTimeImmutable(self::validateYear($year).'-'.$month.'-01');

        return new self($startDate, $startDate->add(new DateInterval('P1M')));
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
        $month = sprintf('%02s', ((self::validateRange($quarter, 1, 4) - 1) * 3) + 1);
        $startDate = new DateTimeImmutable(self::validateYear($year).'-'.$month.'-01');

        return new self($startDate, $startDate->add(new DateInterval('P3M')));
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
        $month = sprintf('%02s', ((self::validateRange($semester, 1, 2) - 1) * 6) + 1);
        $startDate = new DateTimeImmutable(self::validateYear($year).'-'.$month.'-01');

        return new self($startDate, $startDate->add(new DateInterval('P6M')));
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
        $startDate = new DateTimeImmutable(self::validateYear($year).'-01-01');

        return new self($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * String representation of a Period using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString()
    {
        $utc = new DateTimeZone('UTC');

        return $this->startDate->setTimeZone($utc)->format(self::ISO8601)
            .'/'.$this->endDate->setTimeZone($utc)->format(self::ISO8601);
    }

    /**
     * implement JsonSerializable interface
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }

    /**
     * Returns the starting datepoint.
     *
     * @return \DateTimeImmutable
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Returns the ending datepoint.
     *
     * @return \DateTimeImmutable
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * Split the current object into Period objects according to the given interval
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     * @return \Generator
     */
    public function split($interval)
    {
        $res = [];
        $interval = self::validateDateInterval($interval);
        foreach ($this->getDatePeriod($interval) as $startDate) {
            $endDate = $startDate->add($interval);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }
            yield new self($startDate, $endDate);
        }
    }

    /**
     * Tells whether two Period share the same datepoints.
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
     * @param \League\Period\Period|\DateTimeInterface $index
     *
     * @return bool
     */
    public function isAfter($index)
    {
        if ($index instanceof Period) {
            return $this->startDate >= $index->endDate;
        }

        return $this->startDate > self::validateDatePoint($index);
    }

    /**
     * Tells whether a Period is entirely before the specified index
     *
     * @param \League\Period\Period|\DateTimeInterface $index
     *
     * @return bool
     */
    public function isBefore($index)
    {
        if ($index instanceof Period) {
            return $this->endDate <= $index->startDate;
        }

        return $this->endDate <= self::validateDatePoint($index);
    }

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param \League\Period\Period|\DateTimeInterface $index
     *
     * @return bool
     */
    public function contains($index)
    {
        if ($index instanceof Period) {
            return $this->contains($index->startDate) && $this->contains($index->endDate);
        }

        $datetime = self::validateDatePoint($index);

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
        $datetime = $this->startDate->add($period->getDateInterval());
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
     * Returns a new Period object with a new included starting datepoint.
     *
     * @param string|\DateTimeInterface $startDate datepoint
     *
     * @throws \LogicException If $startDate does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function startingOn($startDate)
    {
        return new self(self::validateDatePoint($startDate), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending datepoint.
     *
     * @param string|\DateTimeInterface $endDate datepoint
     *
     * @throws \LogicException If $endDate does not permit the creation of a new object
     *
     * @return \League\Period\Period
     */
    public function endingOn($endDate)
    {
        return new self($this->startDate, self::validateDatePoint($endDate));
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
        return new self($this->startDate, $this->startDate->add(self::validateDateInterval($interval)));
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
        return new self($this->startDate, $this->endDate->add(self::validateDateInterval($interval)));
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
        return new self($this->startDate, $this->endDate->sub(self::validateDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and starting with its ending datepoint.
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

        return new self($this->endDate, $this->endDate->add(self::validateDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and ending with its starting datepoint.
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

        return new self($this->startDate->sub(self::validateDateInterval($interval)), $this->startDate);
    }

    /**
     * Merges one or more Period objects to return a new Period object.
     *
     * The resultant object englobes the largest duration possible.
     *
     * @param \League\Period\Period $arg,... one or more Period objects
     *
     * @throws \RuntimeException If no argument is passed to the function
     *
     * @return \League\Period\Period
     */
    public function merge()
    {
        $periods = func_get_args();
        if (empty($periods)) {
            throw new RuntimeException('At least one Period object must be given.');
        }

        $initiate = clone $this;
        return array_reduce($periods, function (Period $carry, Period $period) {
            if ($carry->startDate > $period->startDate) {
                $carry = $carry->startingOn($period->startDate);
            }
            if ($carry->endDate < $period->endDate) {
                $carry = $carry->endingOn($period->endDate);
            }

            return $carry;
        }, $initiate);
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
     * - be empty if both objects have the same datepoints
     * - contain one Period object if both objects share one datepoint
     * - contain two Period objects if both objects share no datepoint
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
            self::createFromDatepoints($this->startDate, $period->startDate),
            self::createFromDatepoints($this->endDate, $period->endDate),
        ];

        return array_values(array_filter($res, function (Period $period) {
            return $period->startDate != $period->endDate;
        }));
    }

    /**
     * Create a new Period instance given two datepoints
     * The datepoints will be used as to allow the creation of
     * a Period object
     *
     * @param string|\DateTimeInterface $datePoint1 datepoint
     * @param string|\DateTimeInterface $datePoint2 datepoint
     *
     * @return \League\Period\Period
     */
    private static function createFromDatepoints($datePoint1, $datePoint2)
    {
        $datePoint1 = self::validateDatePoint($datePoint1);
        $datePoint2 = self::validateDatePoint($datePoint2);
        if ($datePoint1 > $datePoint2) {
            return new self($datePoint2, $datePoint1);
        }

        return new self($datePoint1, $datePoint2);
    }
}
