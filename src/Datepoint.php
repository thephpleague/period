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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TypeError;
use function filter_var;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
use const FILTER_VALIDATE_INT;

/**
 * League Period Datepoint.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Datepoint
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns a position in time expressed as a DateTimeImmutable object.
     *
     * A datepoint can be
     * <ul>
     * <li>a DateTimeInterface object
     * <li>a integer interpreted as a timestamp
     * <li>a string parsable by DateTime::__construct
     * </ul>
     *
     * @param mixed $datepoint a position in time
     */
    public static function create($datepoint): DateTimeImmutable
    {
        if ($datepoint instanceof DateTimeImmutable) {
            return $datepoint;
        }

        if ($datepoint instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($datepoint);
        }

        if (false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT))) {
            return new DateTimeImmutable('@'.$timestamp);
        }

        if (is_string($datepoint)) {
            return new DateTimeImmutable($datepoint);
        }

        throw new TypeError(sprintf(
            'The datepoint must be expressed using an integer, a string or a DateTimeInterface object %s given',
            is_object($datepoint) ? get_class($datepoint) : gettype($datepoint)
        ));
    }
}
