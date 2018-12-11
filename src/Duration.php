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
use TypeError;
use function filter_var;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
use const FILTER_VALIDATE_INT;

/**
 * League Period Duration.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Duration
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns a continuous portion of time between two datepoints expressed as a DateInterval object.
     *
     * The duration can be
     * <ul>
     * <li>an Period object</li>
     * <li>a DateInterval object</li>
     * <li>an integer interpreted as the duration expressed in seconds.</li>
     * <li>a string parsable by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param mixed $duration a continuous portion of time
     */
    public static function create($duration): DateInterval
    {
        if ($duration instanceof Period) {
            return $duration->getDateInterval();
        }

        if ($duration instanceof DateInterval) {
            return $duration;
        }

        if (false !== ($second = filter_var($duration, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$second.'S');
        }

        if (is_string($duration)) {
            return DateInterval::createFromDateString($duration);
        }

        throw new TypeError(sprintf(
            'The duration must be expressed using an integer, a string, a DateInterval or a Period object %s given',
            is_object($duration) ? get_class($duration) : gettype($duration)
        ));
    }
}
