<?php
/**
* This file is part of the Bakame.tools library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/Bakame.Tools/
* @version 0.2.0
* @package Bakame.Tools
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Bakame;

use DateTime;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;

/**
* A value object class to manipulate Date period
*
* @package Bakame.Tools
* @since 0.1.0
*
*/
final class Period
{
    /**
     * The Range start date
     *
     * @var \DateTime
     */
    private $start;

    /**
     * The Range end date
     *
     * @var \DateTime
     */
    private $end;

    /**
     * The constructor
     */
    private function __construct()
    {

    }

    /**
     * Named Constructor to create a Reporting object
     * from a start and an interver
     *
     * <code>
     *<?php
     * $range = Period::createFromDuration('2012-01-01', '3 MONTH');
     * $range = Period::createFromDuration(new DateTime('2012-01-01'), new DateInterval('P3M'));
     * $range = Period::createFromDuration(new DateTime('2012-01-01'), '3 MONTH');
     * $range = Period::createFromDuration('2012-01-01', new DateInterval('P3M'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string     $datetime start date
     * @param \DateInterval|string $interval interval or a string understood by DateInterval::createFromDateString
     *
     * @return static
     */
    public static function createFromDuration($datetime, $interval)
    {
        $date     = self::validateDateTime($datetime);
        $interval = self::validateDateInterval($interval);
        $range    = new self;
        $range->start = clone $date;
        $range->end   = clone $date;
        $range->end->add($interval);
        if ($range->start > $range->end) {
            throw new LogicException('you must use a positive interval');
        }

        return $range;
    }

    /**
     * Create a Period object from a Year and a Week
     *
     * <code>
     *<?php
     * $range = Period::createFromWeek(2012, 3);
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
        $year = self::validateYear($year);
        $week = self::validateRange($week, 1, 53);

        $start = new DateTime;
        $start->setISODate($year, $week);
        $start->setTime(0, 0, 0);

        return self::createFromDuration(
            $start,
            new DateInterval('P7D')
        );
    }

    /**
     * Create a Period object from a Year and a Month
     *
     * <code>
     *<?php
     * $range = Period::createFromMonth(2012, 11);
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

        return self::createFromDuration(
            $year.'-'.sprintf('%02s', $month).'-01',
            new DateInterval('P1M')
        );
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $range = Period::createFromQuarter(2012, 2);
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

        return self::createFromDuration(
            $year.'-'.sprintf('%02s', $month).'-01',
            new DateInterval('P3M')
        );
    }

    /**
     * Create a Period object from a Year and a Quarter
     *
     * <code>
     *<?php
     * $range = Period::createFromSemester(2012, 1);
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

        return self::createFromDuration(
            $year.'-'.sprintf('%02s', $month).'-01',
            new DateInterval('P6M')
        );
    }

    /**
     * start date setter
     *
     * <code>
     *<?php
     * $range = Period::createFromSemester(2012, 1);
     * $newRange = $range->setStart('2012-02-01');
     * $altRange = $range->setStart(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $datetime
     *
     * @return static
     *
     * @throws \LogicException If the new date is greater than the current end date
     */
    public function setStart($datetime)
    {
        $datetime = self::validateDateTime($datetime);
        if ($datetime > $this->end) {
            throw new LogicException(
                'The start date should be lesser than the current End date'
            );
        }
        $range        = clone $this;
        $range->start = $datetime;

        return $range;
    }

    /**
     * start date getter
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return clone $this->start;
    }

    /**
     * start end setter
     *
     * <code>
     *<?php
     * $range = Period::createFromSemester(2012, 1);
     * $newRange = $range->setEnd('2012-02-01');
     * $altRange = $range->setEnd(new DateTime('2012-02-01'));
     *
     * ?>
     * </code>
     *
     * @param \DateTime|string $datetime
     *
     * @return static
     *
     * @throws \LogicException If the new date is lesser than the current start date
     */
    public function setEnd($datetime)
    {
        $datetime = self::validateDateTime($datetime);
        if ($datetime < $this->start) {
            throw new LogicException(
                'End Date should be greater than the current Start date'
            );
        }
        $range      = clone $this;
        $range->end = $datetime;

        return $range;
    }

    /**
     * end date getter
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return clone $this->end;
    }

    /**
     * return a new Period with the same start
     * but with a different duration
     *
     * @param \DateInterval|string $interval interval or a string understood by DateInterval::createFromDateString
     *
     * @return static
     */
    public function setDuration($interval)
    {
        return self::createFromDuration($this->start, $interval);
    }

    /**
     * return the Period duration as a DateInterval object
     *
     * @return \DateInterval
     */
    public function getDuration()
    {
        return $this->start->diff($this->end);
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
     * @param \DateTime|string $datetime
     *
     * @return boolean
     */
    public function contains($datetime)
    {
        $date = self::validateDateTime($datetime);

        return $date >= $this->start && $date < $this->end;
    }

    /**
     * return the Datetime included in the Period
     * according to a given interval
     *
     * @param \DateInterval|string $ttl
     *
     * @return \DatePeriod
     */
    public function getRange($ttl)
    {
        return new DatePeriod(
            $this->start,
            self::validateDateInterval($ttl),
            $this->end
        );
    }

    /**
     * Validate a DateTime
     *
     * @param mixed $str
     *
     * @return \DateTime
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateTime object
     */
    private static function validateDateTime($str)
    {
        if ($str instanceof Datetime) {
            return $str;
        }

        return new DateTime((string) $str);
    }

    /**
     * Validate a DateInterval
     *
     * @param DateInterval|String $ttl
     *
     * @return DateInterval
     *
     * @throws \RuntimException If The Data can not be converted into a proper DateInterval object
     */
    private static function validateDateInterval($ttl)
    {
        if ($ttl instanceof DateInterval) {
            return $ttl;
        }

        return DateInterval::createFromDateString((string) $ttl);
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
            throw new OutOfRangeException("please verify your value range");
        }

        return $res;
    }
}
