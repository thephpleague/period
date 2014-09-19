<?php

namespace Bakame\Tools;

use DateTime;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;

final class ReportingPeriod
{
    private $startDate;

    private $endDate;

    private function __construct()
    {

    }

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

    private static function validateYear($year)
    {
        $year = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $year) {
            throw new InvalidArgumentException("A Year must be a valid integer");
        }

        return $year;
    }

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

    public function getPeriod(DateInterval $interval)
    {
        return new DatePeriod($this->startDate, $interval, $this->endDate);
    }

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

    public function getStartDate()
    {
        return clone $this->startDate;
    }

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

    public function getEndDate()
    {
        return clone $this->endDate;
    }
}
