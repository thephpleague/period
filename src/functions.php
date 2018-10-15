<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TypeError;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function get_class;
use function gettype;
use function intdiv;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Returns a DateTimeImmutable object.
 */
function datepoint(
    $year_or_datepoint,
    int $month = null,
    int $day = null,
    int $hour = 0,
    int $minute = 0,
    int $second = 0,
    int $microseconds = 0
): DateTimeImmutable {
    if ($year_or_datepoint instanceof DateTimeImmutable) {
        return $year_or_datepoint;
    }

    if ($year_or_datepoint instanceof DateTime) {
        return DateTimeImmutable::createFromMutable($year_or_datepoint);
    }

    $res = filter_var($year_or_datepoint, FILTER_VALIDATE_INT);
    if (false !== $res && null === $month && null === $day) {
        return new DateTimeImmutable('@'.$res);
    }

    if (is_string($year_or_datepoint)) {
        return new DateTimeImmutable($year_or_datepoint);
    }

    if (!is_int($year_or_datepoint)) {
        throw new TypeError(sprintf(
            'The datepoint must be expressed using an integer, a string or a DateTimeInterface object %s given',
            is_object($year_or_datepoint) ? get_class($year_or_datepoint) : gettype($year_or_datepoint)
        ));
    }

    if (null === $month || null === $day) {
        throw new TypeError('The month and day parameters must be integer, null given');
    }

    return (new DateTimeImmutable())
        ->setDate($year_or_datepoint, $month, $day)
        ->setTime($hour, $minute, $second, $microseconds)
    ;
}

/**
 * Returns a DateInval object.
 *
 * The duration can be
 * <ul>
 * <li>a DateInterval object</li>
 * <li>an Interval object</li>
 * <li>an int interpreted as the duration expressed in seconds.</li>
 * <li>a string in a format supported by DateInterval::createFromDateString</li>
 * </ul>
 */
function duration($duration): DateInterval
{
    if ($duration instanceof Period) {
        return $duration->getDateInterval();
    }

    if ($duration instanceof DateInterval) {
        return $duration;
    }

    if (false !== ($res = filter_var($duration, FILTER_VALIDATE_INT))) {
        return new DateInterval('PT'.$res.'S');
    }

    if (is_string($duration)) {
        return DateInterval::createFromDateString($duration);
    }

    throw new TypeError(sprintf(
        'The duration must be expressed using an integer, a string, a DateInterval or a Period object %s given',
        is_object($duration) ? get_class($duration) : gettype($duration)
    ));
}

/**
 * Creates new instance from a starting point and an interval.
 */
function interval_after($datepoint, $duration): Period
{
    $datepoint = datepoint($datepoint);

    return new Period($datepoint, $datepoint->add(duration($duration)));
}

/**
 * Creates new instance from a ending excluded datepoint and an interval.
 */
function interval_before($datepoint, $duration): Period
{
    $datepoint = datepoint($datepoint);

    return new Period($datepoint->sub(duration($duration)), $datepoint);
}

/**
 * Creates new instance where the given duration is simultaneously
 * substracted from and added to the datepoint.
 */
function interval_around($datepoint, $duration): Period
{
    $datepoint = datepoint($datepoint);
    $duration = duration($duration);

    return new Period($datepoint->sub($duration), $datepoint->add($duration));
}

/**
 * Creates new instance from a DatePeriod.
 *
 * @throws Exception If the submitted DatePeriod lacks an end datepoint.
 *                   This is possible if the DatePeriod was created using
 *                   recurrences instead of a end datepoint.
 *                   https://secure.php.net/manual/en/dateperiod.getenddate.php
 */
function interval_from_dateperiod(DatePeriod $datePeriod): Period
{
    $endDate = $datePeriod->getEndDate();
    if ($endDate instanceof DateTimeInterface) {
        return new Period($datePeriod->getStartDate(), $endDate);
    }

    throw new Exception('The submitted DatePeriod object does not contain an end datepoint');
}

/**
 * Creates new instance for a specific year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 */
function year($int_or_datepoint): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0)->setDate((int) $datepoint->format('Y'), 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    $startDate = datepoint($int_or_datepoint, 1, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P1Y')));
}

/**
 * Creates new instance for a specific ISO year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 */
function iso_year($int_or_datepoint): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0);
        $int_or_datepoint = (int) $datepoint->format('o');

        return new Period(
            $datepoint->setISODate($int_or_datepoint, 1),
            $datepoint->setISODate(++$int_or_datepoint, 1)
        );
    }

    $datepoint = datepoint($int_or_datepoint, 1, 1);

    return new Period(
        $datepoint->setISODate($int_or_datepoint, 1),
        $datepoint->setISODate(++$int_or_datepoint, 1)
    );
}

/**
 * Creates new instance for a specific semester in a given year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 * @param int   $index            a semester index from 1 to 2 included
 */
function semester($int_or_datepoint, int $index = 1): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0)->setDate(
            (int) $datepoint->format('Y'),
            (intdiv((int) $datepoint->format('n'), 6) * 6) + 1,
            1
        );

        return new Period($startDate, $startDate->add(new DateInterval('P6M')));
    }

    $startDate = datepoint($int_or_datepoint, (($index - 1) * 6) + 1, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P6M')));
}

/**
 * Creates new instance for a specific quarter in a given year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 * @param int   $index            quarter index
 */
function quarter($int_or_datepoint, int $index = 1): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0)->setDate(
            (int) $datepoint->format('Y'),
            (intdiv((int) $datepoint->format('n'), 3) * 3) + 1,
            1
        );

        return new Period($startDate, $startDate->add(new DateInterval('P3M')));
    }

    $startDate = datepoint($int_or_datepoint, (($index - 1) * 3) + 1, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P3M')));
}

/**
 * Creates new instance for a specific year and month.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 * @param int   $index            month index from 1 to 12 included
 */
function month($int_or_datepoint, int $index = 1): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0)->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1M')));
    }

    $startDate = datepoint($int_or_datepoint, $index, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P1M')));
}

/**
 * Creates new instance for a specific ISO8601 week.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 * @param int   $index            iso week index
 */
function iso_week($int_or_datepoint, int $index = 1): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0);
        $startDate = $datepoint->setISODate((int) $datepoint->format('o'), (int) $datepoint->format('W'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P7D')));
    }

    $startDate = (new DateTimeImmutable())->setTime(0, 0)->setISODate($int_or_datepoint, $index, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P7D')));
}

/**
 * Creates new instance for a specific date.
 *
 * The date is truncated so that the time range starts at midnight
 * according to the date timezone and last a full day.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 */
function day($int_or_datepoint, int $month = 1, int $day = 1): Period
{
    if (!is_int($int_or_datepoint)) {
        $startDate = datepoint($int_or_datepoint)->setTime(0, 0);

        return new Period($startDate, $startDate->add(new DateInterval('P1D')));
    }

    $startDate = datepoint($int_or_datepoint, $month, $day);

    return new Period($startDate, $startDate->add(new DateInterval('P1D')));
}

/**
 * Creates new instance for a specific date and hour.
 *
 * The starting datepoint represents the beginning of the hour
 * The interval is equal to 1 hour
 */
function hour($int_or_datepoint, int $month = 1, int $day = 1, int $hour = 0): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0);

        return new Period($startDate, $startDate->add(new DateInterval('PT1H')));
    }

    $startDate = datepoint($int_or_datepoint, $month, $day, $hour);

    return new Period($startDate, $startDate->add(new DateInterval('PT1H')));
}

/**
 * Creates new instance for a specific date, hour and minute.
 *
 * The starting datepoint represents the beginning of the minute
 * The interval is equal to 1 minute
 */
function minute($int_or_datepoint, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), (int) $datepoint->format('i'));

        return new Period($startDate, $startDate->add(new DateInterval('PT1M')));
    }

    $startDate = datepoint($int_or_datepoint, $month, $day, $hour, $minute);

    return new Period($startDate, $startDate->add(new DateInterval('PT1M')));
}

/**
 * Creates new instance for a specific date, hour, minute and second.
 *
 * The starting datepoint represents the beginning of the second
 * The interval is equal to 1 second
 */
function second($int_or_datepoint, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0, int $second = 0): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(
            (int) $datepoint->format('H'),
            (int) $datepoint->format('i'),
            (int) $datepoint->format('s')
        );

        return new Period($startDate, $startDate->add(new DateInterval('PT1S')));
    }

    $startDate = datepoint($int_or_datepoint, $month, $day, $hour, $minute, $second);

    return new Period($startDate, $startDate->add(new DateInterval('PT1S')));
}

/**
 * Creates new instance for a specific datepoint.
 */
function instant($datepoint): Period
{
    $datepoint = datepoint($datepoint);

    return new Period($datepoint, $datepoint);
}
