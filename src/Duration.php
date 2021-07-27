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
use DateTimeImmutable;
use DateTimeInterface;
use function preg_match;
use function str_pad;

/**
 * League Period Duration.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Duration
{
    private const REGEXP_FLOATING_SECONDS_DATE = '@^(?<interval>.*)(\.)(?<fraction>\d{1,6})$@';
    private const REGEXP_FLOATING_SECONDS_INTERVAL = '@^(?<interval>.*)(\.)(?<fraction>\d{1,6})S$@';
    private const REGEXP_FRACTION_DESIGNATOR = '@^P([^T]+)?(T(?=\d+[HMSF])(\d+H)?(\d+M)?(\d+S)?((?<fraction>\d+)F))?$@';
    private const REGEXP_CHRONOMETER = '@^
        (?<sign>\+|-)?                  # optional sign
        ((?<hour>\d+):)?                # optional hour
        ((?<minute>\d+):)(?<second>\d+) # required minute and second
        (\.(?<fraction>\d{1,6}))?       # optional fraction
    $@x';

    private function __construct(private DateInterval $duration)
    {
    }

    /**
     * @inheritDoc
     *
     * @param array{duration: DateInterval} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['duration']);
    }

    /**
     * Returns a new instance from an interval specification.
     */
    public static function fromIsoString(string $durationIsoString): self
    {
        if (1 === preg_match(self::REGEXP_FLOATING_SECONDS_INTERVAL, $durationIsoString, $matches)) {
            $interval = new DateInterval($matches['interval'].'S');
            $interval->f = (int) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($interval);
        }

        if (1 === preg_match(self::REGEXP_FLOATING_SECONDS_DATE, $durationIsoString, $matches)) {
            $interval = new DateInterval($matches['interval']);
            $interval->f = (int) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($interval);
        }

        if (
            1 === preg_match(self::REGEXP_FRACTION_DESIGNATOR, $durationIsoString, $matches)
            && isset($matches['fraction'])
        ) {
            $interval = new DateInterval(substr($durationIsoString, 0, -strlen($matches['fraction'])-1));
            $interval->f = (int) $matches['fraction'] / 1_000_000;

            return new self($interval);
        }

        return new self(new DateInterval($durationIsoString));
    }

    /**
     * Returns a new instance from a DateInterval object.
     */
    public static function fromInterval(DateInterval $interval): self
    {
        return new self($interval);
    }

    /**
     * Returns a new instance from a seconds.
     */
    public static function fromSeconds(int $second, int $fraction = 0): self
    {
        if (0 > $fraction) {
            throw InvalidTimeRange::dueToInvalidFraction();
        }

        $duration = new DateInterval('PT0S');
        $duration->s = $second;
        $duration->f = $fraction / 1_000_000;

        return new self($duration);
    }

    /**
     * Creates a new instance from a timer string representation.
     *
     * @throws InvalidTimeRange
     */
    public static function fromChronoString(string $chronoString): self
    {
        if (1 !== preg_match(self::REGEXP_CHRONOMETER, $chronoString, $units)) {
            throw InvalidTimeRange::dueToUnknownDurationFormat($chronoString);
        }

        if ('' === $units['hour']) {
            $units['hour'] = '0';
        }

        $units = array_merge(['hour' => '0', 'minute' => '0', 'second' => '0', 'fraction' => '0', 'sign' => '+'], $units);
        $units['fraction'] = str_pad($units['fraction'] ?? '000000', 6, '0');
        $expression = $units['hour'].' hours '.$units['minute'].' minutes '.$units['second'].' seconds '.$units['fraction'].' microseconds';

        $instance = DateInterval::createFromDateString($expression);
        if ('-' === $units['sign']) {
            $instance->h *= -1;
        }

        return new self($instance);
    }

    public static function fromDateString(string $durationString): self
    {
        return new self(DateInterval::createFromDateString($durationString));
    }

    public function toInterval(): DateInterval
    {
        return $this->duration;
    }

    /**
     * Returns a new instance with recalculate properties according to a given datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     */
    public function adjustedTo(DateTimeInterface $date): self
    {
        $date = DateTimeImmutable::createFromInterface($date);

        return new self($date->diff($date->add($this->duration)));
    }
}
