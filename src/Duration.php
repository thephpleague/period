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
use function filter_var;
use const FILTER_VALIDATE_INT;

/**
 * League Period Duration.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Duration extends DateInterval
{
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
            return new self('PT'.$second.'S');
        }

        return self::createFromDateString($duration);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $duration a date with relative parts
     */
    public static function createFromDateString($duration): self
    {
        $duration = parent::createFromDateString($duration);

        $new = new self('PT0S');
        $new->y = $duration->y;
        $new->m = $duration->m;
        $new->d = $duration->d;
        $new->h = $duration->h;
        $new->i = $duration->i;
        $new->s = $duration->s;
        $new->f = $duration->f;

        return $new;
    }
}
