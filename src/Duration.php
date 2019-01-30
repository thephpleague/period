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
use TypeError;
use function filter_var;
use function gettype;
use function is_string;
use function method_exists;
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

    private const REGEXP_CHRONO_FORMAT = '@^
        (
            ((?<hour>\d+):)?
            (?<minute>\d+):
        )?
        (
            (?<second>\d+)
            (\.(?<fraction>\d{1,6}))?
        )
    $@x';

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

        if (!is_string($duration) && !method_exists($duration, '__toString')) {
            throw new TypeError(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($duration)));
        }

        $duration = (string) $duration;
        if (1 === preg_match(self::REGEXP_CHRONO_FORMAT, $duration, $matches)) {
            $matches['hour'] = $matches['hour'] ?? '0';
            $matches['minute'] = $matches['minute'] ?? '0';
            $matches['fraction'] = str_pad($matches['fraction'] ?? '0000000', 6, '0');

            return self::createFromDateString(
                $matches['hour'].' hours '.
                $matches['minute'].' minutes '.
                $matches['second'].' seconds '.
                $matches['fraction'].' microseconds'
            );
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
        return $this->toString($this);
    }

    /**
     * Generates the ISO8601 interval string representation.
     */
    private function toString(DateInterval $interval): string
    {
        $date = 'P';
        foreach (['Y' => $interval->y, 'M' => $interval->m, 'D' => $interval->d] as $key => $value) {
            if (0 !== $value) {
                $date .= $value.$key;
            }
        }

        $time = 'T';
        foreach (['H' => $interval->h, 'M' => $interval->i] as $key => $value) {
            if (0 !== $value) {
                $time .= $value.$key;
            }
        }

        if (0.0 !== $interval->f) {
            $time .= rtrim(sprintf('%f', $interval->s + $interval->f), '0').'S';

            return $date.$time;
        }

        if (0 !== $interval->s) {
            $time .= $interval->s.'S';

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

    /**
     * Returns a new instance with recalculate time and date segments to remove carry over points.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     *
     * @param mixed $datepoint a Reference datepoint
     *                         by default will use the epoch time
     *                         accepts the same input as {@see Duration::create}
     */
    public function withoutCarryOver($datepoint = 0): self
    {
        if (!$datepoint instanceof DateTimeImmutable) {
            $datepoint = Datepoint::create($datepoint);
        }

        $duration = $datepoint->diff($datepoint->add($this));
        if ($this->toString($duration) === $this->toString($this)) {
            return $this;
        }

        return self::create($duration);
    }
}
