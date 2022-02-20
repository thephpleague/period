<?php

/**
 * League.Period (https://period.thephpleague.com)
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
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Datepoint::create
 *
 * @param Datepoint|\DateTimeInterface|int|string $datepoint a datepoint
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
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Duration::create
 *
 * @param Duration|DateInterval|string|int $duration a Duration
 */
function duration($duration): DateInterval
{
    if ($duration instanceof DateInterval) {
        return $duration;
    }

    return Duration::create($duration);
}

/**
 * Creates new instance from a starting point and an interval.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::after
 *
 * @param Datepoint|\DateTimeInterface|int|string $startDate the starting included datepoint
 * @param Duration|DateInterval|string|int        $duration  a Duration
 */
function interval_after($startDate, $duration): Period
{
    return Period::after($startDate, $duration);
}

/**
 * Creates new instance from a ending excluded datepoint and an interval.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::before
 *
 * @param Datepoint|\DateTimeInterface|int|string $endDate  the ending excluded datepoint
 * @param Duration|DateInterval|string|int        $duration a Duration
 */
function interval_before($endDate, $duration): Period
{
    return Period::before($endDate, $duration);
}

/**
 * Creates new instance where the given duration is simultaneously
 * subtracted from and added to the datepoint.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::around
 *
 * @param Datepoint|\DateTimeInterface|int|string $datepoint a datepoint at the center of the returned instance
 * @param Duration|DateInterval|string|int        $duration  a Duration
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
 * @deprecated 4.2.0 This method will be removed in the next major point release
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
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromYear
 * @see Datepoint::getYear
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function year($year_or_datepoint): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromYear($year_or_datepoint);
    }

    return Datepoint::create($year_or_datepoint)->getYear();
}

/**
 * Creates new instance for a specific ISO year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromIsoYear
 * @see Datepoint::getIsoYear
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint an iso year as an int or a datepoint
 */
function iso_year($year_or_datepoint): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromIsoYear($year_or_datepoint);
    }

    return Datepoint::create($year_or_datepoint)->getIsoYear();
}

/**
 * Creates new instance for a specific semester in a given year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromSemester
 * @see Datepoint::getSemester
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function semester($year_or_datepoint, int $semester = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromSemester($year_or_datepoint, $semester);
    }

    return Datepoint::create($year_or_datepoint)->getSemester();
}

/**
 * Creates new instance for a specific quarter in a given year.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromQuarter
 * @see Datepoint::getQuarter
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint an iso year as an int or a datepoint
 */
function quarter($year_or_datepoint, int $quarter = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromQuarter($year_or_datepoint, $quarter);
    }

    return Datepoint::create($year_or_datepoint)->getQuarter();
}

/**
 * Creates new instance for a specific year and month.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromMonth
 * @see Datepoint::getMonth
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function month($year_or_datepoint, int $month = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromMonth($year_or_datepoint, $month);
    }

    return Datepoint::create($year_or_datepoint)->getMonth();
}

/**
 * Creates new instance for a specific ISO8601 week.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromIsoWeek
 * @see Datepoint::getIsoWeek
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint an iso year as an int or a datepoint
 */
function iso_week($year_or_datepoint, int $week = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromIsoWeek($year_or_datepoint, $week);
    }

    return Datepoint::create($year_or_datepoint)->getIsoWeek();
}

/**
 * Creates new instance for a specific date.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::fromIsoWeek
 * @see Datepoint::getDay
 *
 * The date is truncated so that the time range starts at midnight
 * according to the date timezone and last a full day.
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function day($year_or_datepoint, int $month = 1, int $day = 1): Period
{
    if (is_int($year_or_datepoint)) {
        return Period::fromDay($year_or_datepoint, $month, $day);
    }

    return Datepoint::create($year_or_datepoint)->getDay();
}

/**
 * Creates new instance for a specific date and hour.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Datepoint::getHour
 *
 * The starting datepoint represents the beginning of the hour
 * The interval is equal to 1 hour
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function hour($year_or_datepoint, int $month = 1, int $day = 1, int $hour = 0): Period
{
    if (is_int($year_or_datepoint)) {
        $startDate = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, 0);

        return Period::after($startDate, new DateInterval('PT1H'));
    }

    return Datepoint::create($year_or_datepoint)->getHour();
}

/**
 * Creates new instance for a specific date, hour and minute.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Datepoint::getMinute
 *
 * The starting datepoint represents the beginning of the minute
 * The interval is equal to 1 minute
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
 */
function minute($year_or_datepoint, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0): Period
{
    if (is_int($year_or_datepoint)) {
        $startDate = (new DateTimeImmutable())
            ->setDate($year_or_datepoint, $month, $day)
            ->setTime($hour, $minute);

        return Period::after($startDate, new DateInterval('PT1M'));
    }

    return Datepoint::create($year_or_datepoint)->getMinute();
}

/**
 * Creates new instance for a specific date, hour, minute and second.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Datepoint::getSecond
 *
 * The starting datepoint represents the beginning of the second
 * The interval is equal to 1 second
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
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
            ->setTime($hour, $minute, $second);

        return Period::after($startDate, new DateInterval('PT1S'));
    }

    return Datepoint::create($year_or_datepoint)->getSecond();
}

/**
 * Creates new instance for a specific datepoint.
 *
 * DEPRECATION WARNING! This function will be removed in the next major point release
 *
 * @deprecated 4.2.0 This method will be removed in the next major point release
 * @see Period::__construct
 *
 * @param Datepoint|\DateTimeInterface|int|string $year_or_datepoint a year as an int or a datepoint
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
