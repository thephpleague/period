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
final class DatePoint
{
    private function __construct(public readonly DateTimeImmutable $date)
    {
    }

    /**
     *
     *
     * @param array{date: DateTimeImmutable} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['date']);
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

    /**************************************************
     * Period constructors
     **************************************************/

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint second
     *  - the duration is equal to 1 second
     */
    public function second(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date->setTime(
                (int) $this->date->format('H'),
                (int) $this->date->format('i'),
                (int) $this->date->format('s')
            ),
            new DateInterval('PT1S'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint minute
     *  - the duration is equal to 1 minute
     */
    public function minute(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date->setTime(
                (int) $this->date->format('H'),
                (int) $this->date->format('i')
            ),
            new DateInterval('PT1M'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint hour
     *  - the duration is equal to 1 hour
     */
    public function hour(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date->setTime((int) $this->date->format('H'), 0),
            new DateInterval('PT1H'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint day
     *  - the duration is equal to 1 day
     */
    public function day(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date->setTime(0, 0),
            new DateInterval('P1D'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso week
     *  - the duration is equal to 7 days
     */
    public function isoWeek(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date
                ->setTime(0, 0)
                ->setISODate(
                    (int) $this->date->format('o'),
                    (int) $this->date->format('W')
                ),
            new DateInterval('P7D'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint month
     *  - the duration is equal to 1 month
     */
    public function month(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->date->format('Y'),
                    (int) $this->date->format('n'),
                    1
                ),
            new DateInterval('P1M'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint quarter
     *  - the duration is equal to 3 months
     */
    public function quarter(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->date->format('Y'),
                    (intdiv((int) $this->date->format('n'), 3) * 3) + 1,
                    1
                ),
            new DateInterval('P3M'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint semester
     *  - the duration is equal to 6 months
     */
    public function semester(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date
                ->setTime(0, 0)
                ->setDate(
                    (int) $this->date->format('Y'),
                    (intdiv((int) $this->date->format('n'), 6) * 6) + 1,
                    1
                ),
            new DateInterval('P6M'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint year
     *  - the duration is equal to 1 year
     */
    public function year(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        return Period::after(
            $this->date
                ->setTime(0, 0)
                ->setDate((int) $this->date->format('Y'), 1, 1),
            new DateInterval('P1Y'),
            $bounds
        );
    }

    /**
     * Returns a Period instance that datepoint belongs to.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso year
     *  - the duration is equal to 1 iso year
     */
    public function isoYear(Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
    {
        $currentIsoYear = (int) $this->date->format('o');

        return Period::fromDate(
            $this->date->setTime(0, 0)->setISODate($currentIsoYear, 1),
            $this->date->setTime(0, 0)->setISODate($currentIsoYear + 1, 1),
            $bounds
        );
    }

    /**************************************************
     * relation methods
     **************************************************/

    /**
     * Tells whether the instance is before the timeslot.
     */
    public function isBefore(Period $timeSlot): bool
    {
        return $timeSlot->isAfter($this);
    }

    /**
     * Tell whether the instance borders on start the timeslot.
     */
    public function bordersOnStart(Period $timeSlot): bool
    {
        return $this->date == $timeSlot->startDate && !$timeSlot->bounds->isStartIncluded();
    }

    /**
     * Tells whether the instance starts the timeslot.
     */
    public function isStarting(Period $timeSlot): bool
    {
        return $timeSlot->isStartedBy($this->date);
    }

    /**
     * Tells whether the instance is contained within the timeslot.
     */
    public function isDuring(Period $timeSlot): bool
    {
        return $timeSlot->contains($this->date);
    }

    /**
     * Tells whether the instance ends the timeslot.
     */
    public function isEnding(Period $timeSlot): bool
    {
        return $timeSlot->isEndedBy($this->date);
    }

    /**
     * Tells whether the instance borders on end the timeslot.
     */
    public function bordersOnEnd(Period $timeSlot): bool
    {
        return $this->date == $timeSlot->endDate && !$timeSlot->bounds->isEndIncluded();
    }

    /**
     * Tells whether the instance abuts the timeslot.
     */
    public function abuts(Period $timeSlot): bool
    {
        return $this->bordersOnEnd($timeSlot) || $this->bordersOnStart($timeSlot);
    }

    /**
     * Tells whether the instance is after the timeslot.
     */
    public function isAfter(Period $timeSlot): bool
    {
        return $timeSlot->isBefore($this->date);
    }
}
