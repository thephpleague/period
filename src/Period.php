<?php
/**
 * League.Period (http://period.thephpleague.com)
 *
 * @package   League.period
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2014-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version   3.0.0
 * @link      https://github.com/thephpleague/period/
 */
namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use OutOfRangeException;

/**
 * A immutable value object class to manipulate Time Range.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Period implements JsonSerializable
{
    /**
     * DateTime Format to create ISO8601 Interval format
     */
    const DATE_ISO8601 = 'Y-m-d\TH:i:s\Z';

    /**
     * Date Format for timezoneless DateTimeInterface
     */
    const DATE_LOCALE = 'Y-m-d H:i:s';

    /**
     * Period starting included date point.
     *
     * @var DateTimeImmutable
     */
    protected $startDate;

    /**
     * Period ending excluded date point.
     *
     * @var DateTimeImmutable
     */
    protected $endDate;

    /**
     * Create a Period object from a starting point and an interval.
     *
     * @param DateTimeImmutable|DateTime|string $startDate The start date point
     * @param DateInterval|int|string           $interval  The duration. If an int is passed, it is
     *                                                     interpreted as the duration expressed in seconds.
     *                                                     If a string is passed, it must be a format 
     *                                                     supported by `DateInterval::createFromDateString`
     *
     * @return static
     */
    public static function createFromDuration($startDate, $interval)
    {
        $startDate = static::filterDatePoint($startDate);

        return new static($startDate, $startDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Create a Period object from a ending endpoint and an interval.
     *
     * @param DateTimeImmutable|DateTime|string $endDate  The end date point
     * @param DateInterval|int|string           $interval The duration. If an int is passed, it is
     *                                                    interpreted as the duration expressed in seconds.
     *                                                    If a string is passed, it must be a format 
     *                                                    supported by `DateInterval::createFromDateString`
     *
     * @return static
     */
    public static function createFromDurationBeforeEnd($endDate, $interval)
    {
        $endDate = static::filterDatePoint($endDate);

        return new static($endDate->sub(static::filterDateInterval($interval)), $endDate);
    }

    /**
     * Create a Period object from a Year and a Week.
     *
     * @param int $year
     * @param int $week index from 1 to 53
     *
     * @return static
     */
    public static function createFromWeek($year, $week)
    {
        return static::createFromDuration(
            static::validateYear($year).'W'.sprintf('%02d', static::validateRange($week, 1, 53)),
            '1 WEEK'
        );
    }

    /**
     * Validate a year.
     *
     * @param int $year
     *
     * @throws InvalidArgumentException If year is not a valid int
     *
     * @return int
     */
    protected static function validateYear($year)
    {
        $year = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $year) {
            throw new InvalidArgumentException('A Year must be a valid int');
        }

        return $year;
    }

    /**
     * Validate a int according to a range.
     *
     * @param int $value the value to validate
     * @param int $min   the minimum value
     * @param int $max   the maximal value
     *
     * @throws OutOfRangeException If the value is not in the range
     *
     * @return int
     */
    protected static function validateRange($value, $min, $max)
    {
        $res = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if (false === $res) {
            throw new OutOfRangeException('the submitted value is not contained within the valid range');
        }

        return $res;
    }

    /**
     * Create a Period object from a given duration contained in a year
     *
     * @param int $duration
     * @param int $year
     * @param int $index
     *
     * @return static
     */
    protected static function createFromInterval($duration, $year, $index)
    {
        $month = sprintf('%02s', ((static::validateRange($index, 1, 12 / $duration) - 1) * $duration) + 1);
        $startDate = new DateTimeImmutable(static::validateYear($year).'-'.$month.'-01');

        return new static($startDate, $startDate->add(new DateInterval('P'.$duration.'M')));
    }

    /**
     * Create a Period object from a Year and a Month.
     *
     * @param int $year
     * @param int $month Month index from 1 to 12
     *
     * @return static
     */
    public static function createFromMonth($year, $month)
    {
        return static::createFromInterval(1, $year, $month);
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * @param int $year
     * @param int $quarter Quarter Index from 1 to 4
     *
     * @return static
     */
    public static function createFromQuarter($year, $quarter)
    {
        return static::createFromInterval(3, $year, $quarter);
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * @param int $year
     * @param int $semester Semester Index from 1 to 2
     *
     * @return static
     */
    public static function createFromSemester($year, $semester)
    {
        return static::createFromInterval(6, $year, $semester);
    }

    /**
     * Create a Period object from a Year and a Quarter.
     *
     * @param int $year
     *
     * @return static
     */
    public static function createFromYear($year)
    {
        return static::createFromInterval(12, $year, 1);
    }

    /**
     * Create a new Period instance given two endpoints
     * The endpoints will be used as to allow the creation of
     * a Period object
     *
     * @param DateTimeImmutable|DateTime|string $datePoint1 endpoint
     * @param DateTimeImmutable|DateTime|string $datePoint2 endpoint
     *
     * @return Period
     */
    protected static function createFromEndpoints($datePoint1, $datePoint2)
    {
        $startDate = static::filterDatePoint($datePoint1);
        $endDate   = static::filterDatePoint($datePoint2);
        if ($startDate > $endDate) {
            return new static($endDate, $startDate);
        }

        return new static($startDate, $endDate);
    }

    /**
     * Create a new instance.
     *
     * @param DateTimeImmutable|DateTime|string $startDate starting date point
     * @param DateTimeImmutable|DateTime|string $endDate   ending date point
     *
     * @throws LogicException If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = static::filterDatePoint($startDate);
        $endDate   = static::filterDatePoint($endDate);
        if ($startDate > $endDate) {
            throw new LogicException('the ending endpoint must be greater or equal to the starting endpoint');
        }
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Validate a DateTime.
     *
     * @param string|DateTimeImmutable|DateTime $datetime
     *
     * @return DateTimeImmutable
     */
    protected static function filterDatePoint($datetime)
    {
        if ($datetime instanceof DateTimeImmutable) {
            return $datetime;
        }

        if ($datetime instanceof DateTime) {
            return new DateTimeImmutable($datetime->format(static::DATE_LOCALE), $datetime->getTimeZone());
        }

        return new DateTimeImmutable($datetime);
    }

    /**
     * Validate a DateInterval.
     *
     * @param DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                          interpreted as the duration expressed in seconds.
     *                                          If a string is passed, it must be a format 
     *                                          supported by `DateInterval::createFromDateString`
     *
     * @return DateInterval
     */
    protected static function filterDateInterval($interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        }

        if (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        }

        return DateInterval::createFromDateString($interval);
    }

    /**
     * String representation of a Period using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString()
    {
        $utc = new DateTimeZone('UTC');

        return $this->startDate->setTimeZone($utc)->format(static::DATE_ISO8601)
            .'/'.$this->endDate->setTimeZone($utc)->format(static::DATE_ISO8601);
    }

    /**
     * implement JsonSerializable interface
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'startDate' => new DateTime($this->startDate->format(static::DATE_LOCALE), $this->startDate->getTimeZone()),
            'endDate' => new DateTime($this->endDate->format(static::DATE_LOCALE), $this->endDate->getTimeZone()),
        ];
    }

    /**
     * Returns the starting date point.
     *
     * @return DateTimeImmutable
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Returns the ending endpoint.
     *
     * @return DateTimeImmutable
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Returns the Period duration as expressed in seconds
     *
     * @return int
     */
    public function getTimestampInterval()
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * Returns the Period duration as a DateInterval object.
     *
     * @return DateInterval
     */
    public function getDateInterval()
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the Period object.
     *
     * @param DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                          interpreted as the duration expressed in seconds.
     *                                          If a string is passed, it must be a format 
     *                                          supported by `DateInterval::createFromDateString`
     *
     * @return DatePeriod
     */
    public function getDatePeriod($interval)
    {
        return new DatePeriod($this->startDate, static::filterDateInterval($interval), $this->endDate);
    }

    /**
     * Tells whether two Period share the same endpoints.
     *
     * @param Period $period
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
     * @param Period $period
     *
     * @return bool
     */
    public function abuts(Period $period)
    {
        return $this->startDate == $period->getEndDate() || $this->endDate == $period->getStartDate();
    }

    /**
     * Tells whether two Period objects overlaps.
     *
     * @param Period $period
     *
     * @return bool
     */
    public function overlaps(Period $period)
    {
        if ($this->abuts($period)) {
            return false;
        }

        return $this->startDate < $period->getEndDate() && $this->endDate > $period->getStartDate();
    }

    /**
     * Tells whether a Period is entirely after the specified index
     *
     * @param Period|DateTimeImmutable|DateTime $index
     *
     * @return bool
     */
    public function isAfter($index)
    {
        if ($index instanceof Period) {
            return $this->startDate >= $index->getEndDate();
        }

        return $this->startDate > static::filterDatePoint($index);
    }

    /**
     * Tells whether a Period is entirely before the specified index
     *
     * @param Period|DateTimeImmutable|DateTime $index
     *
     * @return bool
     */
    public function isBefore($index)
    {
        if ($index instanceof Period) {
            return $this->endDate <= $index->getStartDate();
        }

        return $this->endDate <= static::filterDatePoint($index);
    }

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param Period|DateTimeImmutable|DateTime $index
     *
     * @return bool
     */
    public function contains($index)
    {
        if ($index instanceof Period) {
            return $this->contains($index->getStartDate()) && $this->contains($index->getEndDate());
        }

        $datetime = static::filterDatePoint($index);

        return $datetime >= $this->startDate && $datetime < $this->endDate;
    }

    /**
     * Compares two Period objects according to their duration.
     *
     * @param Period $period
     *
     * @return int
     */
    public function compareDuration(Period $period)
    {
        $datetime = $this->startDate->add($period->getDateInterval());
        if ($this->endDate > $datetime) {
            return 1;
        }

        if ($this->endDate < $datetime) {
            return -1;
        }

        return 0;
    }

    /**
     * Tells whether the current Period object duration
     * is greater than the submitted one.
     *
     * @param Period $period
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
     * @param Period $period
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
     * @param Period $period
     *
     * @return bool
     */
    public function sameDurationAs(Period $period)
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * Returns a new Period object with a new included starting date point.
     *
     * @param DateTimeImmutable|DateTime|string $startDate date point
     *
     * @return static
     */
    public function startingOn($startDate)
    {
        return new static(static::filterDatePoint($startDate), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending date point.
     *
     * @param DateTimeImmutable|DateTime|string $endDate date point
     *
     * @return static
     */
    public function endingOn($endDate)
    {
        return new static($this->startDate, static::filterDatePoint($endDate));
    }

    /**
     * Returns a new Period object with a new ending date point.
     *
     * @param DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                          interpreted as the duration expressed in seconds.
     *                                          If a string is passed, it must be a format 
     *                                          supported by `DateInterval::createFromDateString`
     *
     * @return static
     */
    public function withDuration($interval)
    {
        return static::createFromDuration($this->startDate, $interval);
    }

    /**
     * Returns a new Period object with an added interval
     *
     * @param DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                          interpreted as the duration expressed in seconds.
     *                                          If a string is passed, it must be a format 
     *                                          supported by `DateInterval::createFromDateString`
     *
     * @return static
     */
    public function add($interval)
    {
        return new static($this->startDate, $this->endDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a Removed interval
     *
     * @param DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                          interpreted as the duration expressed in seconds.
     *                                          If a string is passed, it must be a format 
     *                                          supported by `DateInterval::createFromDateString`
     *
     * @return static
     */
    public function sub($interval)
    {
        return new static($this->startDate, $this->endDate->sub(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and starting with its ending endpoint.
     * If no duration is provided the new Period will be created
     * using the current object duration
     *
     * @param  DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be a format 
     *                                           supported by `DateInterval::createFromDateString`
     * @return static
     */
    public function next($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return static::createFromDuration($this->endDate, $interval);
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and ending with its starting endpoint.
     * If no duration is provided the new Period will have the
     * same duration as the current one
     *
     * @param  DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be a format 
     *                                           supported by `DateInterval::createFromDateString`
     * @return static
     */
    public function previous($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return static::createFromDurationBeforeEnd($this->startDate, $interval);
    }

    /**
     * Merges one or more Period objects to return a new Period object.
     *
     * The resultant object represents the largest duration possible.
     *
     * @param Period ...$arg one or more Period objects
     *
     * @return static
     */
    public function merge(Period $arg)
    {
        $reducer = function (Period $carry, Period $period) {
            if ($carry->getStartDate() > $period->getStartDate()) {
                $carry = $carry->startingOn($period->getStartDate());
            }

            if ($carry->getEndDate() < $period->getEndDate()) {
                $carry = $carry->endingOn($period->getEndDate());
            }

            return $carry;
        };

        return array_reduce(func_get_args(), $reducer, $this);
    }

    /**
     * Split a Period by a given interval
     *
     * @param  DateInterval|int|string $interval The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be a format 
     *                                           supported by `DateInterval::createFromDateString`
     * @return Generator
     */
    public function split($interval)
    {
        $startDate = $this->startDate;
        $interval = static::filterDateInterval($interval);
        do {
            $endDate = $startDate->add($interval);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }
            yield new static($startDate, $endDate);

            $startDate = $endDate;
        } while ($startDate < $this->endDate);
    }

    /**
     * Computes the intersection between two Period objects.
     *
     * @param Period $period
     *
     * @throws LogicException If Both objects do not overlaps
     *
     * @return static
     */
    public function intersect(Period $period)
    {
        if ($this->abuts($period)) {
            throw new LogicException('Both object should not abuts');
        }

        return new static(
            ($period->getStartDate() > $this->startDate) ? $period->getStartDate() : $this->startDate,
            ($period->getEndDate() < $this->endDate) ? $period->getEndDate() : $this->endDate
        );
    }

    /**
     * Computes the gap between two Period objects.
     *
     * @param Period $period
     *
     * @return static
     */
    public function gap(Period $period)
    {
        if ($period->getStartDate() > $this->startDate) {
            return new static($this->endDate, $period->getStartDate());
        }

        return new static($period->getEndDate(), $this->startDate);
    }

    /**
     * Computes the difference between two Period objects which overlap
     * and return an array containing the difference expressed as Period objects
     * The array will:
     * - be empty if both objects have the same endpoints
     * - contain one Period object if both objects share one endpoint
     * - contain two Period objects if both objects share no endpoint
     *
     * @param Period $period
     *
     * @throws LogicException if both object do not overlaps
     *
     * @return Period[]
     */
    public function diff(Period $period)
    {
        if (! $this->overlaps($period)) {
            throw new LogicException('Both Period objects should overlaps');
        }

        $res = [
            static::createFromEndpoints($this->startDate, $period->getStartDate()),
            static::createFromEndpoints($this->endDate, $period->getEndDate()),
        ];

        return array_values(array_filter($res, function (Period $period) {
            return $period->getStartDate() != $period->getEndDate();
        }));
    }

    /**
     * Returns the difference between two Period objects expressed in seconds
     *
     * @param Period $period
     *
     * @return float
     */
    public function timestampIntervalDiff(Period $period)
    {
        return $this->getTimestampInterval() - $period->getTimestampInterval();
    }

    /**
     * Returns the difference between two Period objects expressed in \DateInterval
     *
     * @param Period $period
     *
     * @return DateInterval
     */
    public function dateIntervalDiff(Period $period)
    {
        return $this->endDate->diff($this->withDuration($period->getDateInterval())->endDate);
    }
}
