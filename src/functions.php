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
        'The datepoint must a string or a DateTimeInteface object %s given',
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
    if ($duration instanceof Interval) {
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
        'The interval must an integer, a string or a DateInterval object %s given',
        is_object($duration) ? get_class($duration) : gettype($duration)
    ));
}
