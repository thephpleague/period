<?php
/**
* This file is part of the Bakame.tools library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/csv/
* @version 0.1.0
* @package League.csv
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Bakame\Tools;

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
final class ReportingPeriod
{
    /**
     * The Reporting period start date
     *
     * @var DateTime
     */
    private $startDate;

    /**
     * The Reporting period end date
     *
     * @var DateTime
     */
    private $endDate;

    /**
     * The constructor
     */
    private function __construct()
    {

    }

    /**
     * Named Constructor to create a Reporting object
     * from a startdate and an interver
     *
     * @param DateTime            $start start date
     * @param DateInterval|string $ttl   interval or a string understood by DateInterval::createFromDateString
     *
     * @return static
     */
    public static function createFromDuration(DateTime $start, $ttl)
    {
        $res = new static;
        $res->startDate = clone $start;
        if (! $ttl instanceof DateInterval) {
            $ttl = DateInterval::createFromDateString($ttl);
        }
        $res->endDate = clone $start;
        $res->endDate->add($ttl);
        if ($res->endDate < $res->startDate) {
            throw new LogicException(
                'the End date can not happend earlier that the start date'
            );
        }

        return $res;
    }

    /**
     * Validate a year
     *
     * @param  int $year
     * @return int
     *
     * @throws InvalidArgumentException If year is not a valid integer
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
     * @throws OutOfRangeException If the value is not in the range
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

    /**
     * Create a reportingPeriod object from a Year and a Month
     *
     * @param integer $year
     * @param integer $month
     *
     * @return static
     */
    public static function createFromMonth($year, $month)
    {
        $year  = static::validateYear($year);
        $month = static::validateRange($month, 1, 12);

        $res = new static;
        $res->startDate = new DateTime($year.'-'.sprintf('%02s', $month).'-01');
        $res->endDate = clone $res->startDate;
        $res->endDate->add(new DateInterval('P1M'));

        return $res;
    }

    /**
     * Create a reportingPeriod object from a Year and a Quarter
     *
     * @param integer $year
     * @param integer $quarter
     *
     * @return static
     */
    public static function createFromQuarter($year, $quarter)
    {
        $year = static::validateYear($year);
        $quarter = static::validateRange($quarter, 1, 4);
        $month = ($quarter - 1) * 3;

        $res = new static;
        $res->startDate = new DateTime($year.'-'.sprintf('%02s', $month).'-01');
        $res->endDate   = clone $res->startDate;
        $res->endDate->add(new DateInterval('P3M'));

        return $res;
    }

    /**
     * Create a reportingPeriod object from a Year and a Week
     *
     * @param integer $year
     * @param integer $week
     *
     * @return static
     */
    public static function createFromWeek($year, $week)
    {
        $year = static::validateYear($year);
        $week = static::validateRange($week, 1, 53);

        $start = new DateTime;
        $start->setISODate($year, $week);
        $start->setTime(0, 0, 0);

        $res = new static;
        $res->startDate = $start;
        $res->endDate   = clone $res->startDate;
        $res->endDate->add(new DateInterval('P7D'));

        return $res;
    }

    /**
     * return the Datetime included in the ReportingPeriod
     * according to a given interval
     *
     * @param DateInterval $interval
     *
     * @return DatePeriod
     */
    public function getPeriod(DateInterval $interval)
    {
        return new DatePeriod($this->startDate, $interval, $this->endDate);
    }

    /**
     * start date setter
     *
     * @param DateTime $date
     *
     * @return static
     *
     * @throws LogicException If the new date is greater than the current end date
     */
    public function setStartDate(DateTime $date)
    {
        if ($this->endDate < $date) {
            throw new LogicException(
                'The start Date should be lesser than the current End date'
            );
        }
        $res = clone $this;
        $res->startDate = $date;

        return $res;
    }

    /**
     * start date getter
     *
     * @return DateTime
     */
    public function getStartDate()
    {
        return clone $this->startDate;
    }

    /**
     * start end setter
     *
     * @param DateTime $date
     *
     * @return static
     *
     * @throws LogicException If the new date is lesser than the current start date
     */
    public function setEndDate(DateTime $date)
    {
        if ($date < $this->startDate) {
            throw new LogicException(
                'End Date should be greater than the current Start date'
            );
        }
        $res = clone $this;
        $res->endDate = $date;

        return $res;
    }

    /**
     * end date getter
     *
     * @return DateTime
     */
    public function getEndDate()
    {
        return clone $this->endDate;
    }
}
