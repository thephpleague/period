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
use DateTime;
use DateTimeImmutable;
use TypeError;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Returns a DateTimeImmutable object.
 */
function datepoint($datepoint): DateTimeImmutable
{
    if ($datepoint instanceof DateTimeImmutable) {
        return $datepoint;
    }

    if ($datepoint instanceof DateTime) {
        return DateTimeImmutable::createFromMutable($datepoint);
    }

    if (false !== ($res = filter_var($datepoint, FILTER_VALIDATE_INT))) {
        return new DateTimeImmutable('@'.$res);
    }

    if (is_string($datepoint)) {
        return new DateTimeImmutable($datepoint);
    }

    throw new TypeError(sprintf(
        'The datepoint must be expressed using an integer, a string or a DateTimeInterface object %s given',
        is_object($datepoint) ? get_class($datepoint) : gettype($datepoint)
    ));
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
        'The duration must be expressed using an integer, a string or a DateInterval object %s given',
        is_object($duration) ? get_class($duration) : gettype($duration)
    ));
}

/**
 * Creates new instance from a starting point and an interval.
 */
function interval_after($startDate, $duration): Period
{
    $startDate = datepoint($startDate);

    return new Period($startDate, $startDate->add(duration($duration)));
}

/**
 * Creates new instance from a ending excluded datepoint and an interval.
 */
function interval_before($endDate, $duration): Period
{
    $endDate = datepoint($endDate);

    return new Period($endDate->sub(duration($duration)), $endDate);
}

/**
 * Creates new instance for a specific year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 */
function year($int_or_datepoint): Period
{
    if (is_int($int_or_datepoint)) {
        $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)->setDate($int_or_datepoint, 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    $datepoint = datepoint($int_or_datepoint);
    $startDate = $datepoint->setTime(0, 0, 0, 0)->setDate((int) $datepoint->format('Y'), 1, 1);

    return new Period($startDate, $startDate->add(new DateInterval('P1Y')));
}

/**
 * Creates new instance for a specific ISO year.
 *
 * @param mixed $int_or_datepoint a year as an int or a datepoint
 */
function iso_year($int_or_datepoint): Period
{
    if (is_int($int_or_datepoint)) {
        $datepoint = (new DateTimeImmutable())->setTime(0, 0, 0, 0);

        return new Period(
            $datepoint->setISODate($int_or_datepoint, 1, 1),
            $datepoint->setISODate(++$int_or_datepoint, 1, 1)
        );
    }

    $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
    $int_or_datepoint = (int) $datepoint->format('o');

    return new Period(
        $datepoint->setISODate($int_or_datepoint, 1, 1),
        $datepoint->setISODate(++$int_or_datepoint, 1, 1)
    );
}

/**
 * Creates new instance for a specific semester in a given year.
 *
 * @param mixed    $int_or_datepoint a year as an int or a datepoint
 * @param null|int $index            a semester index from 1 to 2 included
 */
function semester($int_or_datepoint, int $index = null): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0, 0, 0)->setDate(
            (int) $datepoint->format('Y'),
            (intdiv((int) $datepoint->format('n'), 6) * 6) + 1,
            1
        );

        return new Period($startDate, $startDate->add(new DateInterval('P6M')));
    }

    if (null !== $index && 0 < $index && 2 >= $index) {
        $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
            ->setDate($int_or_datepoint, (($index - 1) * 6) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P6M')));
    }

    throw new Exception('The semester index is not contained within the valid range.');
}

/**
 * Creates new instance for a specific quarter in a given year.
 *
 * @param mixed    $int_or_datepoint a year as an int or a datepoint
 * @param null|int $index            quarter index from 1 to 4 included
 */
function quarter($int_or_datepoint, int $index = null): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $startDate = $datepoint->setDate(
            (int) $datepoint->format('Y'),
            (intdiv((int) $datepoint->format('n'), 3) * 3) + 1,
            1
        );

        return new Period($startDate, $startDate->add(new DateInterval('P3M')));
    }

    if (null !== $index && 0 < $index && 4 >= $index) {
        $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
            ->setDate($int_or_datepoint, (($index - 1) * 3) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P3M')));
    }

    throw new Exception('The quarter index is not contained within the valid range.');
}

/**
 * Creates new instance for a specific year and month.
 *
 * @param mixed    $int_or_datepoint a year as an int or a datepoint
 * @param int|null $index            month index from 1 to 12 included
 */
function month($int_or_datepoint, int $index = null): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $startDate = $datepoint->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1M')));
    }

    if (null !== $index && 0 < $index && 12 >= $index) {
        $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)->setDate($int_or_datepoint, $index, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1M')));
    }

    throw new Exception('The month index is not contained within the valid range.');
}

/**
 * Creates new instance for a specific ISO8601 week.
 *
 * @param mixed    $int_or_datepoint a year as an int or a datepoint
 * @param int|null $index            index from 1 to 53 included
 */
function iso_week($int_or_datepoint, int $index = null): Period
{
    if (!is_int($int_or_datepoint)) {
        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $startDate = $datepoint->setISODate((int) $datepoint->format('o'), (int) $datepoint->format('W'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P7D')));
    }

    if (null !== $index && 0 < $index && 53 >= $index) {
        $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)->setISODate($int_or_datepoint, $index, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P7D')));
    }

    throw new Exception('The week index is not contained within the valid range.');
}

/**
 * Creates new instance for a specific date.
 *
 * The date is truncated so that the time range starts at midnight
 * according to the date timezone and last a full day.
 */
function day($datepoint): Period
{
    $startDate = datepoint($datepoint)->setTime(0, 0, 0, 0);

    return new Period($startDate, $startDate->add(new DateInterval('P1D')));
}

/**
 * Creates new instance for a specific date and hour.
 *
 * The starting datepoint represents the beginning of the hour
 * The interval is equal to 1 hour
 */
function hour($datepoint): Period
{
    $datepoint = datepoint($datepoint);
    $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0, 0, 0);

    return new Period($startDate, $startDate->add(new DateInterval('PT1H')));
}

/**
 * Creates new instance for a specific date, hour and minute.
 *
 * The starting datepoint represents the beginning of the minute
 * The interval is equal to 1 minute
 */
function minute($datepoint): Period
{
    $datepoint = datepoint($datepoint);
    $startDate = $datepoint->setTime((int) $datepoint->format('H'), (int) $datepoint->format('i'), 0, 0);

    return new Period($startDate, $startDate->add(new DateInterval('PT1M')));
}

/**
 * Creates new instance for a specific date, hour, minute and second.
 *
 * The starting datepoint represents the beginning of the second
 * The interval is equal to 1 second
 */
function second($datepoint): Period
{
    $datepoint = datepoint($datepoint);
    $startDate = $datepoint->setTime(
        (int) $datepoint->format('H'),
        (int) $datepoint->format('i'),
        (int) $datepoint->format('s'),
        0
    );

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
