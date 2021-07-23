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
use DateTimeZone;
use function date_default_timezone_get;
use function intdiv;

/**
 * League Period Datepoint.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Datepoint
{
    private function __construct(private DateTimeImmutable $datepoint)
    {
    }

    /**
     * @inheritDoc
     */
    public static function __set_state(array $properties)
    {
        return new self($properties['datepoint']);
    }

    public static function fromDate(DateTimeInterface $date): self
    {
        if (!$date instanceof DateTimeImmutable) {
            return new self(DateTimeImmutable::createFromInterface($date));
        }

        return new self($date);
    }

    public static function fromDateString(string $dateString, DateTimeZone $timezone = null): self
    {
        $timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());

        return new self(new DateTimeImmutable($dateString, $timezone));
    }

    public static function fromTimestamp(int $timestamp): self
    {
        return new self((new DateTimeImmutable())->setTimestamp($timestamp));
    }

    public function toDate(): DateTimeImmutable
    {
        return $this->datepoint;
    }

    /**************************************************
     * Period constructors
     **************************************************/

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint second
     *  - the duration is equal to 1 second
     */
    public function second(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint->setTime(
                (int) $this->datepoint->format('H'),
                (int) $this->datepoint->format('i'),
                (int) $this->datepoint->format('s')
            ),
            new DateInterval('PT1S'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint minute
     *  - the duration is equal to 1 minute
     */
    public function minute(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint->setTime(
                (int) $this->datepoint->format('H'),
                (int) $this->datepoint->format('i')
            ),
            new DateInterval('PT1M'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint hour
     *  - the duration is equal to 1 hour
     */
    public function hour(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint->setTime((int) $this->datepoint->format('H'), 0),
            new DateInterval('PT1H'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint day
     *  - the duration is equal to 1 day
     */
    public function day(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint->setTime(0, 0),
            new DateInterval('P1D'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso week
     *  - the duration is equal to 7 days
     */
    public function isoWeek(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint
                ->setTime(0, 0)
                ->setISODate(
                    (int) $this->datepoint->format('o'),
                    (int) $this->datepoint->format('W')
                ),
            new DateInterval('P7D'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint month
     *  - the duration is equal to 1 month
     */
    public function month(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->datepoint->format('Y'),
                    (int) $this->datepoint->format('n'),
                    1
                ),
            new DateInterval('P1M'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint quarter
     *  - the duration is equal to 3 months
     */
    public function quarter(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->datepoint->format('Y'),
                    (intdiv((int) $this->datepoint->format('n'), 3) * 3) + 1,
                    1
                ),
            new DateInterval('P3M'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint semester
     *  - the duration is equal to 6 months
     */
    public function semester(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->datepoint->format('Y'),
                    (intdiv((int) $this->datepoint->format('n'), 6) * 6) + 1,
                    1
                ),
            new DateInterval('P6M'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint year
     *  - the duration is equal to 1 year
     */
    public function year(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->datepoint
                ->setTime(0, 0)
                ->setDate((int) $this->datepoint->format('Y'), 1, 1),
            new DateInterval('P1Y'),
            $boundaries
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso year
     *  - the duration is equal to 1 iso year
     */
    public function isoYear(string $boundaries = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $currentYear = (int) $this->datepoint->format('o');
        $startDate = $this->datepoint->setTime(0, 0)->setISODate($currentYear, 1);

        return Period::fromDatepoint(
            $startDate,
            $startDate->setISODate(++$currentYear, 1),
            $boundaries
        );
    }

    /**************************************************
     * relation methods
     **************************************************/

    /**
     * Tells whether the datepoint is before the interval.
     */
    public function isBefore(Period $interval): bool
    {
        return $interval->isAfter($this);
    }

    /**
     * Tell whether the datepoint borders on start the interval.
     */
    public function bordersOnStart(Period $interval): bool
    {
        return $this->datepoint == $interval->startDate() && $interval->isStartExcluded();
    }

    /**
     * Tells whether the datepoint starts the interval.
     */
    public function isStarting(Period $interval): bool
    {
        return $interval->isStartedBy($this->datepoint);
    }

    /**
     * Tells whether the datepoint is contained within the interval.
     */
    public function isDuring(Period $interval): bool
    {
        return $interval->contains($this->datepoint);
    }

    /**
     * Tells whether the datepoint ends the interval.
     */
    public function isEnding(Period $interval): bool
    {
        return $interval->isEndedBy($this->datepoint);
    }

    /**
     * Tells whether the datepoint borders on end the interval.
     */
    public function bordersOnEnd(Period $interval): bool
    {
        return $this->datepoint == $interval->endDate() && $interval->isEndExcluded();
    }

    /**
     * Tells whether the datepoint abuts the interval.
     */
    public function abuts(Period $interval): bool
    {
        return $this->bordersOnEnd($interval) || $this->bordersOnStart($interval);
    }

    /**
     * Tells whether the datepoint is after the interval.
     */
    public function isAfter(Period $interval): bool
    {
        return $interval->isBefore($this->datepoint);
    }
}
