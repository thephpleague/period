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
use Exception;

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
     * @param array{date: DateTimeImmutable} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['date']);
    }

    public static function fromDate(DateTimeInterface $date): self
    {
        return new self(match (true) {
            $date instanceof DateTimeImmutable => $date,
            default => DateTimeImmutable::createFromInterface($date),
        });
    }

    /**
     * @throws Exception
     */
    public static function fromDateString(string $dateString, DateTimeZone|string $timezone = null): self
    {
        return new self(new DateTimeImmutable($dateString, match (true) {
            $timezone instanceof DateTimeZone => $timezone,
            null === $timezone => new DateTimeZone(date_default_timezone_get()),
            default => new DateTimeZone($timezone),
        }));
    }

    public static function fromTimestamp(int $timestamp): self
    {
        return new self((new DateTimeImmutable())->setTimestamp($timestamp));
    }

    public static function fromFormat(string $format, string $dateString): self
    {
        try {
            $date = DateTimeImmutable::createFromFormat($format, $dateString);
        } catch (Exception $exception) {
            throw InvalidInterval::dueToInvalidDateFormat($format, $dateString, $exception);
        }

        if (false === $date) {
            throw InvalidInterval::dueToInvalidDateFormat($format, $dateString);
        }

        return new self($date);
    }

    /**************************************************
     * Relation methods
     **************************************************/

    /**
     * Tells whether the instance is before the timeslot.
     */
    public function isBefore(Period $timeSlot): bool
    {
        return $timeSlot->isAfter($this);
    }

    /**
     * Tells whether the instance borders on start the timeslot.
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

    /**************************************************
     * Period constructors
     **************************************************/

    /**
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date second
     *  - the duration is equal to 1 second
     */
    public function second(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date minute
     *  - the duration is equal to 1 minute
     */
    public function minute(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date hour
     *  - the duration is equal to 1 hour
     */
    public function hour(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
    {
        return Period::after(
            $this->date->setTime((int) $this->date->format('H'), 0),
            new DateInterval('PT1H'),
            $bounds
        );
    }

    /**
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date day
     *  - the duration is equal to 1 day
     */
    public function day(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
    {
        return Period::after(
            $this->date->setTime(0, 0),
            new DateInterval('P1D'),
            $bounds
        );
    }

    /**
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date iso week day
     *  - the duration is equal to 7 days
     */
    public function isoWeek(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date month
     *  - the duration is equal to 1 month
     */
    public function month(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date quarter
     *  - the duration is equal to 3 months
     */
    public function quarter(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date semester
     *  - the duration is equal to 6 months
     */
    public function semester(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date year
     *  - the duration is equal to 1 year
     */
    public function year(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
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
     * Returns a Period instance to which the current instance date belongs to.
     *
     *  - the starting date endpoint represents the beginning of the current date iso year
     *  - the duration is equal to 1 iso year
     */
    public function isoYear(Bounds $bounds = Bounds::IncludeStartExcludeEnd): Period
    {
        $currentIsoYear = (int) $this->date->format('o');

        return Period::fromDate(
            $this->date->setTime(0, 0)->setISODate($currentIsoYear, 1),
            $this->date->setTime(0, 0)->setISODate($currentIsoYear + 1, 1),
            $bounds
        );
    }
}
