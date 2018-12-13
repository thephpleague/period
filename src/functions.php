<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use function is_int;

/**
 * Returns a DateTimeImmutable object.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Datepoint::create
 */
function datepoint($datepoint): DateTimeImmutable
{
    if ($datepoint instanceof DateTimeImmutable) {
        return $datepoint;
    }

    return Datepoint::create($datepoint);
}

/**
 * Returns a DateInval object.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Duration::create
 */
function duration($duration): DateInterval
{
    return Duration::create($duration);
}

/**
 * Creates new instance from a starting point and an interval.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::after
 */
function interval_after($datepoint, $duration): Period
{
    return Period::after($datepoint, $duration);
}

/**
 * Creates new instance from a ending excluded datepoint and an interval.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::before
 */
function interval_before($datepoint, $duration): Period
{
    return Period::before($datepoint, $duration);
}

/**
 * Creates new instance where the given duration is simultaneously
 * substracted from and added to the datepoint.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::around
 */
function interval_around($datepoint, $duration): Period
{
    return Period::around($datepoint, $duration);
}

/**
 * Creates new instance from a DatePeriod.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromDatePeriod
 */
function interval_from_dateperiod(DatePeriod $datePeriod): Period
{
    return Period::fromDatePeriod($datePeriod);
}

/**
 * Creates new instance for a specific year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromYear
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function year($year_or_datepoint): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromYear($year_or_datepoint);
    }

    return Datepoint::create($year_or_datepoint)->extractYear();
}

/**
 * Creates new instance for a specific ISO year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromIsoYear
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint an iso year as an int or a datepoint
 */
function iso_year($year_or_datepoint): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromIsoYear($year_or_datepoint);
    }

    return Datepoint::create($year_or_datepoint)->extractIsoYear();
}

/**
 * Creates new instance for a specific semester in a given year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromSemester
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function semester($year_or_datepoint, int $semester = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromSemester($year_or_datepoint, $semester);
    }

    return Datepoint::create($year_or_datepoint)->extractSemester();
}

/**
 * Creates new instance for a specific quarter in a given year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromQuarter
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint an iso year as an int or a datepoint
 */
function quarter($year_or_datepoint, int $quarter = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromQuarter($year_or_datepoint, $quarter);
    }

    return Datepoint::create($year_or_datepoint)->extractQuarter();
}

/**
 * Creates new instance for a specific year and month.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromMonth
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function month($year_or_datepoint, int $month = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromMonth($year_or_datepoint, $month);
    }

    return Datepoint::create($year_or_datepoint)->extractMonth();
}

/**
 * Creates new instance for a specific ISO8601 week.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromIsoWeek
 * @see Period::fromCalendar
 *
 * @param mixed $year_or_datepoint an iso year as an int or a datepoint
 */
function iso_week($year_or_datepoint, int $week = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromIsoWeek($year_or_datepoint, $week);
    }

    return Datepoint::create($year_or_datepoint)->extractIsoWeek();
}

/**
 * Creates new instance for a specific date.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromIsoWeek
 * @see Period::fromCalendar
 *
 * The date is truncated so that the time range starts at midnight
 * according to the date timezone and last a full day.
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function day($year_or_datepoint, int $month = 1, int $day = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromDay($year_or_datepoint, $month, $day);
    }

    return Datepoint::create($year_or_datepoint)->extractDay();
}

/**
 * Creates new instance for a specific date and hour.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromCalendar
 *
 * The starting datepoint represents the beginning of the hour
 * The interval is equal to 1 hour
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function hour($year_or_datepoint, int $month = 1, int $day = 1, int $hour = 0): Period
{
    if (is_int($year_or_datepoint)) {
        $startDate = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, 0)
        ;

        return Period::after($startDate, new DateInterval('PT1H'));
    }

    $datepoint = Datepoint::create($year_or_datepoint);
    $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0);

    return Period::after($startDate, new DateInterval('PT1H'));
}

/**
 * Creates new instance for a specific date, hour and minute.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromCalendar
 *
 * The starting datepoint represents the beginning of the minute
 * The interval is equal to 1 minute
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function minute($year_or_datepoint, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0): Period
{
    if (is_int($year_or_datepoint)) {
        $startDate = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, $minute)
        ;

        return Period::after($startDate, new DateInterval('PT1M'));
    }

    $datepoint = Datepoint::create($year_or_datepoint);
    $startDate = $datepoint->setTime(
        (int) $datepoint->format('H'),
        (int) $datepoint->format('i'),
        0
    );

    return Period::after($startDate, new DateInterval('PT1M'));
}

/**
 * Creates new instance for a specific date, hour, minute and second.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated deprecated since version 4.2
 * @see Period::fromCalendar
 *
 * The starting datepoint represents the beginning of the second
 * The interval is equal to 1 second
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function second(
    $year_or_datepoint,
    int $month = 1,
    int $day = 1,
    int $hour = 0,
    int $minute = 0,
    int $second = 0
): Period {
    if (is_int($year_or_datepoint)) {
        $startDate = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, $minute, $second)
        ;

        return Period::after($startDate, new DateInterval('PT1S'));
    }

    $datepoint = Datepoint::create($year_or_datepoint);
    $startDate = $datepoint->setTime(
        (int) $datepoint->format('H'),
        (int) $datepoint->format('i'),
        (int) $datepoint->format('s')
    );

    return Period::after($startDate, new DateInterval('PT1S'));
}

/**
 * Creates new instance for a specific datepoint.
 *
 * @param mixed $year_or_datepoint a year as an int or a datepoint
 */
function instant(
    $year_or_datepoint,
    int $month = 1,
    int $day = 1,
    int $hour = 0,
    int $minute = 0,
    int $second = 0,
    int $microsecond = 0
): Period {
    if (is_int($year_or_datepoint)) {
        $year_or_datepoint = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, $minute, $second, $microsecond);
    }

    return new Period($year_or_datepoint, $year_or_datepoint);
}
