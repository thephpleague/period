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
use function filter_var;
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
    private const REGEXP_MICROSECONDS_INTERVAL_SPEC = '@^(?<interval>.*)(\.|,)(?<fraction>\d{1,6})S$@';
    private const REGEXP_MICROSECONDS_DATE_SPEC = '@^(?<interval>.*)(\.)(?<fraction>\d{1,6})$@';

    private const REGEXP_CHRONO_FORMAT = '@^
        (?<sign>\+|-)?                  # optional sign
        ((?<hour>\d+):)?                # optional hour
        ((?<minute>\d+):)(?<second>\d+) # required minute and second
        (\.(?<fraction>\d{1,6}))?       # optional fraction
    $@x';

    private const REGEXP_TIME_FORMAT = '@^
        (?<sign>\+|-)?                               # optional sign
        (?<hour>\d+)(:(?<minute>\d+))                # required hour and minute
        (:(?<second>\d+)(\.(?<fraction>\d{1,6}))?)?  # optional second and fraction
    $@x';

    private function __construct(private DateInterval $duration)
    {
    }

    /**
     * Returns a new instance from an Interval specification.
     */
    public static function fromIsoString(string $interval_spec): self
    {
        if (1 === preg_match(self::REGEXP_MICROSECONDS_INTERVAL_SPEC, $interval_spec, $matches)) {
            $duration = new DateInterval($matches['interval'].'S');
            $duration->f = (float) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($duration);
        }

        if (1 === preg_match(self::REGEXP_MICROSECONDS_DATE_SPEC, $interval_spec, $matches)) {
            $duration = new DateInterval($matches['interval']);
            $duration->f = (float) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($duration);
        }

        return new self(new DateInterval($interval_spec));
    }

    /**
     * Returns a new instance from a DateInterval object.
     */
    public static function fromDateInterval(DateInterval $duration): self
    {
        return new self($duration);
    }

    /**
     * Returns a new instance from a seconds.
     */
    public static function fromSeconds(float $seconds): self
    {
        $invert = 0 > $seconds;
        if ($invert) {
            $seconds = $seconds * -1;
        }

        $duration = new DateInterval('PT'.(int) $seconds.'S');
        $duration->f = $seconds - $duration->s;
        if ($invert) {
            $duration->invert = 1;
        }

        return new self($duration);
    }

    /**
     * Creates a new instance from a timer string representation.
     *
     * @throws InvalidTimeRange
     */
    public static function fromChronoString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_CHRONO_FORMAT, $duration, $units)) {
            throw InvalidTimeRange::dueToUnknownDuratiomFormnat($duration);
        }

        if ('' === $units['hour']) {
            $units['hour'] = '0';
        }

        return self::fromTimeUnits($units);
    }

    /**
     * Creates a new instance from a time string representation following RDBMS specification.
     *
     * @throws InvalidTimeRange
     */
    public static function fromTimeString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_TIME_FORMAT, $duration, $units)) {
            throw InvalidTimeRange::dueToUnknownDuratiomFormnat($duration);
        }

        return self::fromTimeUnits($units);
    }

    /**
     * Creates an instance from DateInterval units.
     *
     * @param array<string,string> $units
     */
    private static function fromTimeUnits(array $units): self
    {
        $units = $units + ['hour' => '0', 'minute' => '0', 'second' => '0', 'fraction' => '0', 'sign' => '+'];

        $units['fraction'] = str_pad($units['fraction'] ?? '000000', 6, '0');

        $expression = $units['hour'].' hours '
            .$units['minute'].' minutes '
            .$units['second'].' seconds '
            .$units['fraction'].' microseconds';

        $instance = DateInterval::createFromDateString($expression);
        if ('-' === $units['sign']) {
            $instance->invert = 1;
        }

        return new self($instance);
    }

    /**
     * @inheritDoc
     *
     * @param mixed $duration a date with relative parts
     *
     * @return self|false
     */
    public static function fromDateString($duration)
    {
        return new self(DateInterval::createFromDateString($duration));
    }

    public function toDateInterval(): DateInterval
    {
        return $this->duration;
    }

    /**
     * Returns a new instance with recalculate properties according to a given datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     *
     * @param mixed $datepoint a reference datepoint {@see \League\Period\Datepoint::create}
     */
    public function adjustedTo($datepoint): self
    {
        $datepoint = match (true) {
            $datepoint instanceof Datepoint => $datepoint->toDateTimeImmutable(),
            $datepoint instanceof DateTimeImmutable => $datepoint,
            $datepoint instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($datepoint),
            false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT)) => (new DateTimeImmutable())->setTimestamp($datepoint),
            default => new DateTimeImmutable($datepoint),
        };

        return new self($datepoint->diff($datepoint->add($this->duration)));
    }
}
