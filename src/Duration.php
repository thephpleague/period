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
 * @psalm-immutable
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
        (?<sign>\+|-)?
        (((?<hour>\d+):)?(?<minute>\d+):)?
        ((?<second>\d+)(\.(?<fraction>\d{1,6}))?)
    $@x';

    /**
     * New instance.
     *
     * Returns a new instance from an Interval specification
     *
     * @psalm-pure note: changing the internal factory is an edge case not covered by purity invariants,
     *             but under constant factory setups, this method operates in functionally pure manners
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
     *
     * @throws TypeError if the duration type is not a supported
     *
     * @psalm-pure note: changing the internal factory is an edge case not covered by purity invariants,
     *             but under constant factory setups, this method operates in functionally pure manners
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
        if (1 !== preg_match(self::REGEXP_CHRONO_FORMAT, $duration, $matches)) {
            $new = self::createFromDateString($duration);
            if ($new !== false) {
                return $new;
            }

            throw new Exception(sprintf('Unknown or bad format (%s)', $duration));
        }

        $matches['hour'] = $matches['hour'] ?? '0';
        if ('' === $matches['hour']) {
            $matches['hour'] = '0';
        }

        $matches['minute'] = $matches['minute'] ?? '0';
        if ('' === $matches['minute']) {
            $matches['minute'] = '0';
        }

        $matches['fraction'] = str_pad($matches['fraction'] ?? '0000000', 6, '0');
        $expression = $matches['hour'].' hours '.
            $matches['minute'].' minutes '.
            $matches['second'].' seconds '.$matches['fraction'].' microseconds';

        $instance = self::createFromDateString($expression);
        if (false === $instance) {
            throw new Exception(sprintf('Unknown or bad format (%s)', $expression));
        }

        if ('-' === $matches['sign']) {
            $instance->invert = 1;
        }

        return $instance;
    }

    /**
     * @inheritDoc
     *
     * @param mixed $duration a date with relative parts
     *
     * @return static|false
     *
     * @psalm-pure note: changing the internal factory is an edge case not covered by purity invariants,
     *             but under constant factory setups, this method operates in functionally pure manners
     */
    public static function createFromDateString($duration): self
    {
        $duration = parent::createFromDateString($duration);
        if (false === $duration) {
            return $duration;
        }

        $new = new self('PT0S');
        foreach ($duration as $name => $value) {
            $new->$name = $value;
        }

        return $new;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated deprecated since version 4.5
     * @see ::format
     *
     * Returns the ISO8601 interval string representation.
     *
     * Microseconds fractions are included
     */
    public function __toString(): string
    {
        $date = 'P';
        foreach (['Y' => 'y', 'M' => 'm', 'D' => 'd'] as $key => $value) {
            if (0 !== $this->$value) {
                $date .= '%'.$value.$key;
            }
        }

        $time = 'T';
        foreach (['H' => 'h', 'M' => 'i'] as $key => $value) {
            if (0 !== $this->$value) {
                $time .= '%'.$value.$key;
            }
        }

        if (0.0 !== $this->f) {
            $second = $this->s + $this->f;
            if (0 > $this->s) {
                $second = $this->s - $this->f;
            }

            $second = rtrim(sprintf('%f', $second), '0');

            return $this->format($date.$time).$second.'S';
        }

        if (0 !== $this->s) {
            $time .= '%sS';

            return $this->format($date.$time);
        }

        if ('T' !== $time) {
            return $this->format($date.$time);
        }

        if ('P' !== $date) {
            return $this->format($date);
        }

        return 'PT0S';
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated deprecated since version 4.6
     * @see ::adjustedTo
     *
     * Returns a new instance with recalculate time and date segments to remove carry over points.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     *
     * @param mixed $reference_date a reference datepoint {@see \League\Period\Datepoint::create}
     */
    public function withoutCarryOver($reference_date): self
    {
        return $this->adjustedTo($reference_date);
    }

    /**
     * Returns a new instance with recalculate properties according to a given datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     *
     * @param mixed $reference_date a reference datepoint {@see \League\Period\Datepoint::create}
     */
    public function adjustedTo($reference_date): self
    {
        if (!$reference_date instanceof DateTimeImmutable) {
            $reference_date = Datepoint::create($reference_date);
        }

        return self::create($reference_date->diff($reference_date->add($this)));
    }
}
