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
use Exception;
use InvalidArgumentException;
use Throwable;

use function preg_match;
use function str_pad;
use function strlen;
use function substr;

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

    private const REGEXP_TIME_FORMAT = '@^
        (?<sign>\+|-)?                              # optional sign
        (?<hour>\d+)(:(?<minute>\d+))               # required hour and minute
        (:(?<second>\d+)(\.(?<fraction>\d{1,6}))?)? # optional second and fraction
    $@x';

    private function __construct(public readonly DateInterval $dateInterval)
    {
    }

    /**
     * @param array{dateInterval: DateInterval} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['dateInterval']);
    }

    /**
     * Returns a new instance from an interval specification.
     *
     * @throws Exception
     */
    public static function fromIsoString(string $duration): self
    {
        if (1 === preg_match(self::REGEXP_FLOATING_SECONDS_INTERVAL, $duration, $matches)) {
            $interval = new DateInterval($matches['interval'].'S');
            $interval->f = (int) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($interval);
        }

        if (1 === preg_match(self::REGEXP_FLOATING_SECONDS_DATE, $duration, $matches)) {
            $interval = new DateInterval($matches['interval']);
            $interval->f = (int) str_pad($matches['fraction'], 6, '0') / 1_000_000;

            return new self($interval);
        }

        if (1 === preg_match(self::REGEXP_FRACTION_DESIGNATOR, $duration, $matches)
            && isset($matches['fraction'])
        ) {
            $interval = new DateInterval(substr($duration, 0, -strlen($matches['fraction']) - 1));
            $interval->f = (int) $matches['fraction'] / 1_000_000;

            return new self($interval);
        }

        return new self(new DateInterval($duration));
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
     *
     * @throws InvalidArgumentException
     */
    public static function fromSeconds(int $seconds, int $fractions = 0): self
    {
        if (0 > $fractions) {
            throw new InvalidArgumentException('The fraction should be a valid positive integer or zero.');
        }

        $duration = new DateInterval('PT0S');
        $duration->s = $seconds;
        $duration->f = $fractions / 1_000_000;

        return new self($duration);
    }

    /**
     * Creates a new instance from a timer string representation.
     *
     * @throws InvalidArgumentException
     */
    public static function fromChronoString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_CHRONOMETER, $duration, $matches)) {
            throw new InvalidArgumentException('Unknown or bad format `'.$duration.'`.');
        }

        return self::fromUnits([
            'hour' => '' === $matches['hour'] ? '0' : $matches['hour'],
            'minute' => $matches['minute'],
            'second' => $matches['second'],
            'fraction' => $matches['fraction'] ?? null,
            'sign' => $matches['sign'] ?? null,
        ]);
    }

    /**
     * Creates a new instance from a time string representation.
     *
     * @throws InvalidArgumentException
     */
    public static function fromTimeString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_TIME_FORMAT, $duration, $matches)) {
            throw new InvalidArgumentException('Unknown or bad format ('.$duration.')');
        }

        return self::fromUnits([
            'hour' => $matches['hour'] ?? null,
            'minute' => $matches['minute'] ?? null,
            'second' => $matches['second'] ?? '0',
            'fraction' => $matches['fraction'] ?? null,
            'sign' => $matches['sign'] ?? null,
        ]);
    }

    /**
     * Creates a new instance from a date time string like representation.
     *
     * @throws InvalidArgumentException
     */
    public static function fromDateString(string $duration): self
    {
        try {
            $dateInterval = DateInterval::createFromDateString($duration);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException('Unknown or bad format `'.$duration.'`.', 0, $exception);
        }

        if (false === $dateInterval) {
            throw new InvalidArgumentException('Unknown or bad format `'.$duration.'`.');
        }

        return new self($dateInterval);
    }

    /**
     * @param array{hour: ?string, minute: ?string, second: ?string, fraction: ?string, sign: ?string} $units
     */
    private static function fromUnits(array $units): self
    {
        $units += ['hour' => '0', 'minute' => '0', 'second' => '0', 'fraction' => '0', 'sign' => '+'];
        $units['fraction'] = str_pad($units['fraction'] ?? '000000', 6, '0');
        if ('-' === $units['sign']) {
            $units['hour'] = '-'.$units['hour'];
        }

        return self::fromDateString(
            $units['hour'].' hours '.$units['minute'].' minutes '.$units['second'].' seconds '.$units['fraction'].' microseconds'
        );
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
        if (!$date instanceof DateTimeImmutable) {
            $date = DateTimeImmutable::createFromInterface($date);
        }

        return new self($date->diff($date->add($this->dateInterval)));
    }
}
