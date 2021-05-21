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

    public static function fromDate(DateTimeInterface $datepoint): self
    {
        if (!$datepoint instanceof DateTimeImmutable) {
            return new self(DateTimeImmutable::createFromInterface($datepoint));
        }

        return new self($datepoint);
    }

    public static function fromDateString(string $datepoint, DateTimeZone $timezone = null): self
    {
        $timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());

        return new self(new DateTimeImmutable($datepoint, $timezone));
    }

    public static function fromTimestamp(int $timestamp): self
    {
        return new self((new DateTimeImmutable())->setTimestamp($timestamp));
    }

    public function toDateTimeImmutable(): DateTimeImmutable
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
    public function second(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->datepoint->setTime(
            (int) $this->datepoint->format('H'),
            (int) $this->datepoint->format('i'),
            (int) $this->datepoint->format('s')
        );

        return Period::fromDatepoint($datepoint, $datepoint->add(new DateInterval('PT1S')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint minute
     *  - the duration is equal to 1 minute
     */
    public function minute(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->datepoint->setTime((int) $this->datepoint->format('H'), (int) $this->datepoint->format('i'), 0);

        return Period::fromDatepoint($datepoint, $datepoint->add(new DateInterval('PT1M')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint hour
     *  - the duration is equal to 1 hour
     */
    public function hour(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->datepoint->setTime((int) $this->datepoint->format('H'), 0);

        return Period::fromDatepoint($datepoint, $datepoint->add(new DateInterval('PT1H')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint day
     *  - the duration is equal to 1 day
     */
    public function day(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->datepoint->setTime(0, 0);

        return Period::fromDatepoint($datepoint, $datepoint->add(new DateInterval('P1D')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso week
     *  - the duration is equal to 7 days
     */
    public function isoWeek(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this->datepoint
            ->setTime(0, 0)
            ->setISODate(
                (int) $this->datepoint->format('o'),
                (int) $this->datepoint->format('W'),
                1
            );

        return Period::fromDatepoint($startDate, $startDate->add(new DateInterval('P7D')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint month
     *  - the duration is equal to 1 month
     */
    public function month(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this->datepoint
            ->setTime(0, 0)
            ->setDate(
                (int) $this->datepoint->format('Y'),
                (int) $this->datepoint->format('n'),
                1
            );

        return Period::fromDatepoint($startDate, $startDate->add(new DateInterval('P1M')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint quarter
     *  - the duration is equal to 3 months
     */
    public function quarter(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this->datepoint
            ->setTime(0, 0)
            ->setDate(
                (int) $this->datepoint->format('Y'),
                (intdiv((int) $this->datepoint->format('n'), 3) * 3) + 1,
                1
            );

        return Period::fromDatepoint($startDate, $startDate->add(new DateInterval('P3M')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint semester
     *  - the duration is equal to 6 months
     */
    public function semester(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this->datepoint
            ->setTime(0, 0)
            ->setDate(
                (int) $this->datepoint->format('Y'),
                (intdiv((int) $this->datepoint->format('n'), 6) * 6) + 1,
                1
            );

        return Period::fromDatepoint($startDate, $startDate->add(new DateInterval('P6M')), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint year
     *  - the duration is equal to 1 year
     */
    public function year(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $year = (int) $this->datepoint->format('Y');
        $datepoint = $this->datepoint->setTime(0, 0);

        return Period::fromDatepoint($datepoint->setDate($year, 1, 1), $datepoint->setDate(++$year, 1, 1), $boundaryType);
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso year
     *  - the duration is equal to 1 iso year
     */
    public function isoYear(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $year = (int) $this->datepoint->format('o');
        $datepoint = $this->datepoint->setTime(0, 0);

        return Period::fromDatepoint($datepoint->setISODate($year, 1, 1), $datepoint->setISODate(++$year, 1, 1), $boundaryType);
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
