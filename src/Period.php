<?php
/**
* This file is part of the Period library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/period/
* @version 2.0.0
* @package League.Period
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Period;

use DateTime;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;

/**
* A value object class to manipulate Time Range
*/
final class Period
{
    /**
     * The date period starting included endpoint
     *
     * @var \DateTime
     */
    private $start;

    /**
     * The date period ending excluded endpoint
     *
     * @var \DateTime
     */
    private $end;

    /**
     * The Constructor
     *
     * $period = new Period('2012-01-01', '2012-02-17');
     *
     * @param \DateTime|string $start start datetime
     * @param \DateTime|string $end   end datetime
     *
     * @throws \LogicException If $start is greater than $end
     */
    public function __construct($start, $end)
    {
        $start = self::validateDateTime($start);
        $end   = self::validateDateTime($end);
        if ($start > $end) {
            throw new LogicException(
                'the ending endpoint must be greater or equal to the starting endpoint'
            );
        }
        $this->start = clone $start;
        $this->end   = clone $end;
    }

    /**
     * Validate a DateTime
     *
     * @param \DateTime|string $datetime
     *
     * @return \DateTime
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateTime object
     */
    private static function validateDateTime($datetime)
    {
        if ($datetime instanceof DateTime) {
            return $datetime;
        }

        return new DateTime((string) $datetime);
    }

    /**
     * returns the starting DateTime
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return clone $this->start;
    }

    /**
     * returns the ending DateTime
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return clone $this->end;
    }

    /**
     * return the Period duration as a DateInterval object
     *
     * @param boolean $get_as_seconds If used and set to true, the method will return an integer
     *                                which represents the duration in seconds instead of a
     *                                \DateInterval object
     *
     * @return \DateInterval|integer
     */
    public function getDuration($get_as_seconds = false)
    {
        if ($get_as_seconds) {
            return $this->end->getTimestamp() - $this->start->getTimestamp();
        }

        return $this->start->diff($this->end);
    }

    /**
     * return the Datetime included in the Period
     * according to a given interval
     *
     * @param  \DateInterval|integer|string $interval The interval. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return \DatePeriod
     */
    public function getRange($interval)
    {
        return new DatePeriod(
            $this->start,
            self::validateDateInterval($interval),
            $this->end
        );
    }

    /**
     * Validate a DateInterval
     *
     * @param  \DateInterval|integer|string $interval The interval. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return \DateInterval
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateInterval object
     */
    private static function validateDateInterval($interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        }
        $res = filter_var(
            $interval,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => 0))
        );
        if (false !== $res) {
            return new DateInterval('PT'.$res.'S');
        }

        return DateInterval::createFromDateString((string) $interval);
    }

    /**
     * Tell whether both Period object are equals in duration AND endpoints
     *
     * @param Period $period
     *
     * @return true
     */
    public function sameValueAs(Period $period)
    {
        return $this->start == $period->start && $this->end == $period->end;
    }

    /**
     * Tell whether two Period objects overlaps
     *
     * @param Period $period
     *
     * @return boolean
     */
    public function overlaps(Period $period)
    {
        return $this->contains($period->start) || $this->contains($period->end);
    }

    /**
     * Tells whether a DateTime is contained within the Period object
     *
     * <code>
     *<?php
     *   $obj = Period::createFromMonth(2014, 3);
     *   $obj->contains('2014-03-30'); //return true
     *   $obj->contains('2014-04-01'); //return false
     *
     * ?>
     * </code>
     *
     * @param Period|\DateTime|string $index
     *
     * @return boolean
     */
    public function contains($index)
    {
        if ($index instanceof Period) {
            return $this->contains($index->start) && $this->contains($index->end);
        }

        $date = self::validateDateTime($index);

        return $date >= $this->start && $date < $this->end;
    }

    /**
     * Returns the difference between two Period objects
     *
     * @param Period  $period
     * @param boolean $get_as_seconds If used and set to true, the method will return an integer
     *                                which represents the duration in seconds instead of a
     *                                \DateInterval object
     *
     * @return \DateInterval|integer
     */
    public function durationDiff(Period $period, $get_as_seconds = false)
    {
        $diff =   $this->end->getTimestamp()
                - $this->start->getTimestamp()
                - $period->end->getTimestamp()
                + $period->start->getTimestamp();
        if (! $get_as_seconds) {
            $res = new DateInterval('PT'.abs($diff).'S');
            if (0 > $diff) {
                $res->invert = 1;
            }

            return $res;
        }

        return $diff;
    }

    /**
     * Compare two Period objects according to their duration
     *
     * @param Period $period
     *
     * @return integer
     */
    public function compareDuration(Period $period)
    {
        $date = new DateTime;
        $alt  = clone $date;
        $date->add($this->start->diff($this->end));
        $alt->add($period->start->diff($period->end));
        if ($date > $alt) {
            return 1;
        } elseif ($date < $alt) {
            return -1;
        }

        return 0;
    }

    /**
     * Tell whether the given object duration is less than the current Period object
     *
     * @param Period $period
     *
     * @return true
     */
    public function durationGreaterThan(Period $period)
    {
        return 1 === $this->compareDuration($period);
    }

    /**
     * Tell whether the given object duration is greater than the current Period object
     *
     * @param Period $period
     *
     * @return true
     */
    public function durationLessThan(Period $period)
    {
        return -1 === $this->compareDuration($period);
    }

    /**
     * Tell whether the given object duration is equals to the current Period object
     *
     * @param Period $period
     *
     * @return true
     */
    public function sameDurationAs(Period $period)
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * Create a Period object from a starting point and an interval
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
     * @param  \DateTime|string             $start    start date
     * @param  \DateInterval|integer|string $duration The duration. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return static
     */
    public static function createFromDuration($start, $duration)
    {
        $start = self::validateDateTime($start);
        $end   = clone $start;
        $end->add(self::validateDateInterval($duration));

        return new self($start, $end);
    }

    /**
     * Create a Period object from a Year and a Week
     *
     * <code>
     *<?php
     * $period = Period::createFromWeek(2012, 3);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $week index from 1 to 53
     *
     * @return static
     */
    public static function createFromWeek($year, $week)
    {
        $start = new DateTime;
        $start->setISODate(self::validateYear($year), self::validateRange($week, 1, 53));
        $start->setTime(0, 0, 0);

        return self::createFromDuration($start, '1 WEEK');
    }

    /**
     * Validate a year
     *
     * @param integer $year
     *
     * @return integer
     *
     * @throws \InvalidArgumentException If year is not a valid integer
     */
    private static function validateYear($year)
    {
        $year = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $year) {
            throw new InvalidArgumentException("A Year must be a valid integer");
        }

        return $year;
    }

    /**
     * Validate a integer according to a range
     *
     * @param integer $value the value to validate
     * @param integer $min   the minimun value
     * @param integer $max   the maximal value
     *
     * @return integer the validated value
     *
     * @throws \OutOfRangeException If the value is not in the range
     */
    private static function validateRange($value, $min, $max)
    {
        $res = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => $min, 'max_range' => $max))
        );
        if (false === $res) {
            throw new OutOfRangeException(
                "the submitted value is not contained within the valid range"
            );
        }

        return $res;
    }

    /**
     * Create a Period object from a Year and a Month
     *
     * <code>
     *<?php
     * $period = Period::createFromMonth(2012, 11);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $month Month index from 1 to 12
     *
     * @return static
     */
    public static function createFromMonth($year, $month)
    {
        $year  = self::validateYear($year);
        $month = self::validateRange($month, 1, 12);

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '1 MONTH');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromQuarter(2012, 2);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $quarter Quarter Index from 1 to 4
     *
     * @return static
     */
    public static function createFromQuarter($year, $quarter)
    {
        $year    = self::validateYear($year);
        $quarter = self::validateRange($quarter, 1, 4);
        $month   = (($quarter - 1) * 3) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '3 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromBiennal(2012, 1);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     * @param integer $semester Semester Index from 1 to 2
     *
     * @return static
     */
    public static function createFromSemester($year, $semester)
    {
        $year     = self::validateYear($year);
        $semester = self::validateRange($semester, 1, 2);
        $month    = (($semester - 1) * 6) + 1;

        return self::createFromDuration($year.'-'.sprintf('%02s', $month).'-01', '6 MONTHS');
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $period = Period::createFromYear(2012);
     *
     * ?>
     * </code>
     *
     * @param integer $year
     *
     * @return static
     */
    public static function createFromYear($year)
    {
        return self::createFromDuration(self::validateYear($year).'-01-01', '1 YEAR');
    }

    /**
     * returns a new Period object with a new includedd starting endpoint
     *
     * <code>
     *<?php
     * $period = Period::createFromSemester(2012, 1);
     * $newRange = $period->startingOn('2012-02-01');
     * $altRange = $period->startingOn(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $start
     *
     * @return static
     */
    public function startingOn($start)
    {
        return new self(self::validateDateTime($start), $this->end);
    }

    /**
     * returns a new Period object with a new excluded ending endpoint
     *
     * <code>
     *<?php
     * $period = Period::createFromSemester(2012, 1);
     * $newRange = $period->endingOn('2012-02-01');
     * $altRange = $period->endingOn(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $end
     *
     * @return static
     */
    public function endingOn($end)
    {
        return new self($this->start, self::validateDateTime($end));
    }

    /**
     * returns a new Period object with a new ending DateTime
     *
     * @param  \DateInterval|integer|string $duration The duration. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return static
     */
    public function withDuration($duration)
    {
        return self::createFromDuration($this->start, $duration);
    }

    /**
     * Add an interval to the current Period object
     *
     * @param  \DateInterval|integer|string $duration The duration. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return static
     */
    public function add($interval)
    {
        $end = clone $this->end;

        return new self($this->start, $end->add(self::validateDateInterval($interval)));
    }

    /**
     * remove an interval to the current Period object
     *
     * @param  \DateInterval|integer|string $duration The duration. If an integer is passed, it is
     *                                                interpreted as the duration expressed in
     *                                                seconds. If a string is passed, it must be
     *                                                parsable by `DateInterval::createFromDateString`
     * @return static
     */
    public function sub($interval)
    {
        $end = clone $this->end;

        return new self($this->start, $end->sub(self::validateDateInterval($interval)));
    }

    /**
     * Merge one or more Period objects to return a new Period object
     * that englobes the biggest duration possible
     *
     * @param Period $arg,... one or more Period objects
     *
     * @return static
     */
    public function merge()
    {
        $args = func_get_args();
        if (! $args) {
            throw new InvalidArgumentException('A Period object is missing');
        }
        $res = $this;
        array_walk($args, function (Period $period) use (&$res) {
            $start = $res->getStart();
            if ($start > $period->getStart()) {
                $start = $period->getStart();
            }
            $end = $res->getEnd();
            if ($end < $period->getEnd()) {
                $end = $period->getEnd();
            }

            $res = new Period($start, $end);
        });

        return $res;
    }

    /**
     * Compute the intersection between two Period objects
     *
     * @param Period $period
     *
     * @return static
     */
    public function intersect(Period $period)
    {
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
}
