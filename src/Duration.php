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
use function array_pop;
use function explode;
use function filter_var;
use function preg_grep;
use function preg_match;
use function property_exists;
use function rtrim;
use function sprintf;
use function str_pad;
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
    private const REGEXP_MICROSECONDS_INTERVAL_SPEC = '@^(?<interval>.*)(\.|,)(?<fraction>\d{1,6})S$@';

    private const REGEXP_MICROSECONDS_DATE_SPEC = '@^(?<interval>.*)(\.)(?<fraction>\d{1,6})$@';

    private const REGEXP_CHRONO_SECOND = '@^\d+(\.\d+)?$@';

    private const REGEXP_CHRONO_UNIT = '@^\d+$@';

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
    public static function create($duration): self
    {
        if ($duration instanceof Period) {
            $duration = $duration->getDateInterval();
        }

        if ($duration instanceof DateInterval) {
            $new = new self('PT0S');
            foreach ($duration as $name => $value) {
                if (property_exists($new, $name)) {
                    $new->$name = $value;
                }
            }

            return $new;
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
        foreach ($duration as $name => $value) {
            $new->$name = $value;
        }

        return $new;
    }

    /**
     * Sets up a Duration from the string representation of a chronometer.
     *
     * The chronometer string is a representation of time
     * without any date part following the below format
     * HH:MM:SS.f
     *
     * The chronometer unit are always positive or equal to 0
     * except for the second unit which accept a fraction part.
     *
     * @throws InvalidDurationFormat If the chrono string can not be parsed
     */
    public static function fromChrono(string $chrono): self
    {
        $parts = explode(':', $chrono, 3);
        $second = array_pop($parts);
        if (null === $second || 1 !== preg_match(self::REGEXP_CHRONO_SECOND, $second)) {
            throw new InvalidDurationFormat(sprintf('%s: Unknown or bad chrono string format (%s)', __METHOD__, $chrono));
        }

        if ([] === $parts) {
            return new self('PT'.$second.'S');
        }

        if ($parts !== preg_grep(self::REGEXP_CHRONO_UNIT, $parts)) {
            throw new InvalidDurationFormat(sprintf('%s: Unknown or bad chrono string format (%s)', __METHOD__, $chrono));
        }

        if (isset($parts[1])) {
            return new self('PT'.$parts[0].'H'.$parts[1].'M'.$second.'S');
        }

        return new self('PT'.$parts[0].'M'.$second.'S');
    }

    /**
     * New instance.
     *
     * Returns a new instance from an Interval specification
     */
    public function __construct(string $interval_spec)
    {
        if (1 === preg_match(self::REGEXP_MICROSECONDS_INTERVAL_SPEC, $interval_spec, $matches)) {
            parent::__construct($matches['interval'].'S');
            $this->f = (float) str_pad($matches['fraction'], 6, '0') / 1e6;
            return;
        }

        if (1 === preg_match(self::REGEXP_MICROSECONDS_DATE_SPEC, $interval_spec, $matches)) {
            parent::__construct($matches['interval']);
            $this->f = (float) str_pad($matches['fraction'], 6, '0') / 1e6;
            return;
        }

        parent::__construct($interval_spec);
    }

    /**
     * Returns the ISO8601 interval string representation.
     *
     * Microseconds fractions are included
     */
    public function __toString(): string
    {
        $date = 'P';
        foreach (['Y' => $this->y, 'M' => $this->m, 'D' => $this->d] as $key => $value) {
            if (0 !== $value) {
                $date .= $value.$key;
            }
        }

        $time = 'T';
        foreach (['H' => $this->h, 'M' => $this->i] as $key => $value) {
            if (0 !== $value) {
                $time .= $value.$key;
            }
        }

        if (0.0 !== $this->f) {
            $time .= rtrim(sprintf('%f', $this->s + $this->f), '0').'S';

            return $date.$time;
        }

        if (0 !== $this->s) {
            $time .= $this->s.'S';

            return $date.$time;
        }

        if ('T' !== $time) {
            return $date.$time;
        }

        if ('P' !== $date) {
            return $date;
        }

        return 'PT0S';
    }
}
