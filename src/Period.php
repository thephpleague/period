<?php
/**
 * League.Period (http://period.thephpleague.com)
 *
 * @package   League.period
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2014-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version   3.4.0
 * @link      https://github.com/thephpleague/period/
 */
namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
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
     *
     * @internal
     */
    const DATE_ISO8601 = 'Y-m-d\TH:i:s\Z';

    /**
     * Date Format for timezoneless DateTimeInterface
     *
     * @internal
     */
    const DATE_LOCALE = 'Y-m-d H:i:s.u';

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
     * Create a new instance.
     *
     * @param DateTimeInterface|string $startDate starting date point
     * @param DateTimeInterface|string $endDate   ending date point
     *
     * @throws LogicException If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = static::filterDatePoint($startDate);
        $endDate = static::filterDatePoint($endDate);
        if ($startDate > $endDate) {
            throw new LogicException(
                'The ending datepoint must be greater or equal to the starting datepoint'
            );
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Validate a DateTime.
     *
     * @param DateTimeInterface|string $datetime
     *
     * @return DateTimeImmutable
     */
    protected static function filterDatePoint($datetime)
    {
        if ($datetime instanceof DateTimeImmutable) {
            return $datetime;
        }

        if ($datetime instanceof DateTime) {
            return static::convertDateTime($datetime);
        }

        return new DateTimeImmutable($datetime);
    }

    /**
     * Convert a DateTime object into a DateTimeImmutable object
     *
     * @param DateTime $datetime
     *
     * @return DateTimeImmutable
     */
    protected static function convertDateTime(DateTime $datetime)
    {
        static $useFromMutable;

        if (null === $useFromMutable) {
            $useFromMutable = method_exists(new DateTimeImmutable(), 'createFromMutable');
        }

        if ($useFromMutable) {
            return DateTimeImmutable::createFromMutable($datetime);
        }

        return new DateTimeImmutable($datetime->format(self::DATE_LOCALE), $datetime->getTimeZone());
    }

    /**
     * @inheritdoc
     */
    public static function __set_state(array $period)
    {
        return new static($period['startDate'], $period['endDate']);
    }

    /**
     * Create a Period object for a specific day
     *
     * The date is truncated so that the Time range starts at midnight according to the date timezone.
     * The duration is equivalent to one full day.
     *
     * @param DateTimeInterface|string $day
     *
     * @return static
     */
    public static function createFromDay($day)
    {
        $date = static::filterDatePoint($day);

        $startDate = $date->createFromFormat(
            self::DATE_LOCALE,
            $date->format('Y-m-d').' 00:00:00.000000',
            $date->getTimeZone()
        );

        return new static($startDate, $startDate->add(new DateInterval('P1D')));
    }

    /**
     * Create a Period object from a starting point and an interval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateTimeInterface|string $startDate The start date point
     * @param mixed                    $interval  The interval
     *
     * @return static
     */
    public static function createFromDuration($startDate, $interval)
    {
        $startDate = static::filterDatePoint($startDate);

        return new static($startDate, $startDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Validate a DateInterval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param mixed $interval The interval
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
     * Create a Period object from a ending datepoint and an interval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateTimeInterface|string $endDate  The start date point
     * @param mixed                    $interval The interval
     *
     * @return static
     */
    public static function createFromDurationBeforeEnd($endDate, $interval)
    {
        $endDate = static::filterDatePoint($endDate);

        return new static($endDate->sub(static::filterDateInterval($interval)), $endDate);
    }

    /**
     * Create a Period object for a specific week
     *
     * @param int $year
     * @param int $week index from 1 to 53
     *
     * @return static
     */
    public static function createFromWeek($year, $week)
    {
        $week = static::validateYear($year).'W'.sprintf('%02d', static::validateRange($week, 1, 53));
        $startDate = new DateTimeImmutable($week);

        return new static($startDate, $startDate->add(new DateInterval('P1W')));
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
     * Create a Period object for a specific month
     *
     * @param int $year
     * @param int $month Month index from 1 to 12
     *
     * @return static
     */
    public static function createFromMonth($year, $month)
    {
        return static::createFromYearInterval(1, $year, $month);
    }

    /**
     * Create a Period object for a specific interval in a given year
     *
     * @param int $interval
     * @param int $year
     * @param int $index
     *
     * @return static
     */
    protected static function createFromYearInterval($interval, $year, $index)
    {
        $month = sprintf('%02s', ((static::validateRange($index, 1, 12 / $interval) - 1) * $interval) + 1);
        $startDate = new DateTimeImmutable(static::validateYear($year).'-'.$month.'-01');

        return new static($startDate, $startDate->add(new DateInterval('P'.$interval.'M')));
    }

    /**
     * Create a Period object for a specific quarter
     *
     * @param int $year
     * @param int $quarter Quarter Index from 1 to 4
     *
     * @return static
     */
    public static function createFromQuarter($year, $quarter)
    {
        return static::createFromYearInterval(3, $year, $quarter);
    }

    /**
     * Create a Period object for a specific semester
     *
     * @param int $year
     * @param int $semester Semester Index from 1 to 2
     *
     * @return static
     */
    public static function createFromSemester($year, $semester)
    {
        return static::createFromYearInterval(6, $year, $semester);
    }

    /**
     * Create a Period object for a specific Year
     *
     * @param int $year
     *
     * @return static
     */
    public static function createFromYear($year)
    {
        $startDate = new DateTimeImmutable(static::validateYear($year).'-01-01');

        return new static($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * String representation of a Period using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString()
    {
        $utc = new DateTimeZone('UTC');

        return $this->startDate->setTimeZone($utc)->format(self::DATE_ISO8601)
            .'/'.$this->endDate->setTimeZone($utc)->format(self::DATE_ISO8601);
    }

    /**
     * implement JsonSerializable interface
     *
     * @return DateTime[]
     */
    public function jsonSerialize()
    {
        return [
            'startDate' => new DateTime(
                $this->startDate->format(self::DATE_LOCALE),
                $this->startDate->getTimeZone()
            ),
            'endDate' => new DateTime(
                $this->endDate->format(self::DATE_LOCALE),
                $this->endDate->getTimeZone()
            ),
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
     * Returns the ending datepoint.
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
     * @return float
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
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @param int $option can be set to DatePeriod::EXCLUDE_START_DATE
     *                    to exclude the start date from the set of
     *                    recurring dates within the period.
     *
     * @return DatePeriod
     */
    public function getDatePeriod($interval, $option = 0)
    {
        return new DatePeriod($this->startDate, static::filterDateInterval($interval), $this->endDate, $option);
    }

    /**
     * Tells whether two Period share the same datepoints.
     *
     * @param Period $period
     *
     * @return bool
     */
    public function sameValueAs(Period $period)
    {
        return $this->startDate == $period->getStartDate() && $this->endDate == $period->getEndDate();
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
     * @param Period|DateTimeInterface|string $index
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
     * @param Period|DateTimeInterface|string $index
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
     * @param Period|DateTimeInterface|string $index
     *
     * @return bool
     */
    public function contains($index)
    {
        if ($index instanceof Period) {
            return $this->containsPeriod($index);
        }

        return $this->containsDatePoint($index);
    }

    /**
     * Tells whether a Period object is fully contained within
     * the current Period object.
     *
     * @param Period $period
     *
     * @return bool
     */
    protected function containsPeriod(Period $period)
    {
        $endDate = $period->getEndDate();

        return $this->contains($period->getStartDate())
            && ($endDate >= $this->startDate && $endDate <= $this->endDate);
    }

    /**
     * Tells whether a datepoint is fully contained within
     * the current Period object.
     *
     * @param DateTimeInterface|string $datepoint
     *
     * @return bool
     */
    protected function containsDatePoint($datepoint)
    {
        $datetime = static::filterDatePoint($datepoint);

        return ($datetime >= $this->startDate && $datetime < $this->endDate)
            || ($datetime == $this->startDate && $datetime == $this->endDate);
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
     * @param DateTimeInterface|string $startDate date point
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
     * @param DateTimeInterface|string $endDate date point
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
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function withDuration($interval)
    {
        return new static($this->startDate, $this->startDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a new starting date point.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function withDurationBeforeEnd($interval)
    {
        return new static($this->endDate->sub(static::filterDateInterval($interval)), $this->endDate);
    }

    /**
     * Returns a new Period object with a new starting date point
     * moved forward or backward by the given interval
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function moveStartDate($interval)
    {
        return new static($this->startDate->add(static::filterDateInterval($interval)), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending date point
     * moved forward or backward by the given interval
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function moveEndDate($interval)
    {
        return new static($this->startDate, $this->endDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function move($interval)
    {
        $interval = static::filterDateInterval($interval);

        return new static($this->startDate->add($interval), $this->endDate->add($interval));
    }

    /**
     * Returns a new Period object with an added interval
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 3.3.0
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function add($interval)
    {
        return $this->moveEndDate($interval);
    }

    /**
     * Returns a new Period object with a Removed interval
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 3.3.0
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function sub($interval)
    {
        return new static($this->startDate, $this->endDate->sub(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and starting with its ending datepoint.
     * If no duration is provided the new Period will be created
     * using the current object duration
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function next($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return new static($this->endDate, $this->endDate->add(static::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object adjacent to the current Period
     * and ending with its starting datepoint.
     * If no duration is provided the new Period will have the
     * same duration as the current one
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return static
     */
    public function previous($interval = null)
    {
        if (is_null($interval)) {
            $interval = $this->getDateInterval();
        }

        return new static($this->startDate->sub(static::filterDateInterval($interval)), $this->startDate);
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
     * Split a Period by a given interval (from startDate to endDate)
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return Generator|Period[]
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
     * Split a Period by a given interval (from endDate to startDate)
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return Generator|Period[]
     */
    public function splitBackwards($interval)
    {
        $endDate = $this->endDate;
        $interval = static::filterDateInterval($interval);
        do {
            $startDate = $endDate->sub($interval);
            if ($startDate < $this->startDate) {
                $startDate = $this->startDate;
            }
            yield new static($startDate, $endDate);

            $endDate = $startDate;
        } while ($endDate > $this->startDate);
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
        if (! $this->overlaps($period)) {
            throw new LogicException('Both object should at least overlaps');
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

    /**
     * Computes the difference between two overlapsing Period objects
     *
     * Returns an array containing the difference expressed as Period objects
     * The array will:
     *
     * <ul>
     * <li>be empty if both objects have the same datepoints</li>
     * <li>contain one Period object if both objects share one datepoint</li>
     * <li>contain two Period objects if both objects share no datepoint</li>
     * </ul>
     *
     * @param Period $period
     *
     * @throws LogicException if both object do not overlaps
     *
     * @return Period[]
     */
    public function diff(Period $period)
    {
        if (!$this->overlaps($period)) {
            throw new LogicException('Both Period objects should overlaps');
        }

        $res = [
            static::createFromDatepoints($this->startDate, $period->getStartDate()),
            static::createFromDatepoints($this->endDate, $period->getEndDate()),
        ];

        $filter = function (Period $period) {
            return $period->getStartDate() != $period->getEndDate();
        };

        return array_values(array_filter($res, $filter));
    }

    /**
     * Create a new instance given two datepoints
     *
     * The datepoints will be used as to allow the creation of
     * a Period object
     *
     * @param DateTimeInterface|string $datePoint1 datepoint
     * @param DateTimeInterface|string $datePoint2 datepoint
     *
     * @return Period
     */
    protected static function createFromDatepoints($datePoint1, $datePoint2)
    {
        $startDate = static::filterDatePoint($datePoint1);
        $endDate = static::filterDatePoint($datePoint2);
        if ($startDate > $endDate) {
            return new static($endDate, $startDate);
        }

        return new static($startDate, $endDate);
    }
}
