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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ReturnTypeWillChange;
use function filter_var;
use function intdiv;
use const FILTER_VALIDATE_INT;

/**
 * League Period Datepoint.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Datepoint extends DateTimeImmutable
{
    /**
     * Returns a position in time expressed as a DateTimeImmutable object.
     *
     * A datepoint can be
     * <ul>
     * <li>a DateTimeInterface object
     * <li>a integer interpreted as a timestamp
     * <li>a string parsable by DateTime::__construct
     * </ul>
     *
     * @param DateTimeInterface|Datepoint|int|string $datepoint a position in time
     */
    public static function create($datepoint): self
    {
        if ($datepoint instanceof DateTimeInterface) {
            return new self($datepoint->format('Y-m-d H:i:s.u'), $datepoint->getTimezone());
        }

        $timestamp = $datepoint;
        if (is_int($timestamp) && false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT))) {
            return new self('@'.$timestamp);
        }

        return new self($datepoint);
    }

    /**
     * @inheritDoc
     *
     * @param string       $format
     * @param string       $datetime
     * @param DateTimeZone $timezone
     *
     * @return static|false
     */
    #[ReturnTypeWillChange]
    public static function createFromFormat($format, $datetime, ?DateTimeZone $timezone = null)
    {
        $datepoint = parent::createFromFormat($format, $datetime, $timezone);
        if (false !== $datepoint) {
            return self::create($datepoint);
        }

        return $datepoint;
    }

    /**
     * @inheritDoc
     *
     * @param DateTime $datetime
     *
     * @return static
     */
    public static function createFromMutable($datetime): self
    {
        return self::create(parent::createFromMutable($datetime));
    }

    /**************************************************
     * interval constructors
     **************************************************/

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint second
     *  - the duration is equal to 1 second
     */
    public function second(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->setTime(
            (int) $this->format('H'),
            (int) $this->format('i'),
            (int) $this->format('s')
        );

        return new Period($datepoint, $datepoint->add(new DateInterval('PT1S')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::second()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint second
     *  - the duration is equal to 1 second
     */
    public function getSecond(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->second($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint minute
     *  - the duration is equal to 1 minute
     */
    public function minute(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->setTime((int) $this->format('H'), (int) $this->format('i'), 0);

        return new Period($datepoint, $datepoint->add(new DateInterval('PT1M')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::minute()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint minute
     *  - the duration is equal to 1 minute
     */
    public function getMinute(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->minute();
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint hour
     *  - the duration is equal to 1 hour
     */
    public function hour(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->setTime((int) $this->format('H'), 0);

        return new Period($datepoint, $datepoint->add(new DateInterval('PT1H')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::hour()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint hour
     *  - the duration is equal to 1 hour
     */
    public function getHour(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->hour($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint day
     *  - the duration is equal to 1 day
     */
    public function day(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $datepoint = $this->setTime(0, 0);

        return new Period($datepoint, $datepoint->add(new DateInterval('P1D')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::day()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint day
     *  - the duration is equal to 1 day
     */
    public function getDay(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->day($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso week
     *  - the duration is equal to 7 days
     */
    public function isoWeek(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setISODate((int) $this->format('o'), (int) $this->format('W'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P7D')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::isoWeek()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso week
     *  - the duration is equal to 7 days
     */
    public function getIsoWeek(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->isoWeek($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint month
     *  - the duration is equal to 1 month
     */
    public function month(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (int) $this->format('n'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1M')), $boundaryType);
    }


    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::month()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint month
     *  - the duration is equal to 1 month
     */
    public function getMonth(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->month($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint quarter
     *  - the duration is equal to 3 months
     */
    public function quarter(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (intdiv((int) $this->format('n'), 3) * 3) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P3M')), $boundaryType);
    }


    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::quarter()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint quarter
     *  - the duration is equal to 3 months
     */
    public function getQuarter(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->quarter($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint semester
     *  - the duration is equal to 6 months
     */
    public function semester(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (intdiv((int) $this->format('n'), 6) * 6) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P6M')), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::semester()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint semester
     *  - the duration is equal to 6 months
     */
    public function getSemester(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->semester($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint year
     *  - the duration is equal to 1 year
     */
    public function year(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $year = (int) $this->format('Y');
        $datepoint = $this->setTime(0, 0);

        return new Period($datepoint->setDate($year, 1, 1), $datepoint->setDate(++$year, 1, 1), $boundaryType);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::year()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint year
     *  - the duration is equal to 1 year
     */
    public function getYear(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->year($boundaryType);
    }

    /**
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso year
     *  - the duration is equal to 1 iso year
     */
    public function isoYear(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        $year = (int) $this->format('o');
        $datepoint = $this->setTime(0, 0);

        return new Period(
            $datepoint->setISODate($year, 1, 1),
            $datepoint->setISODate(++$year, 1, 1),
            $boundaryType
        );
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Datepoint::isoYear()
     *
     * Returns a Period instance.
     *
     *  - the starting datepoint represents the beginning of the current datepoint iso year
     *  - the duration is equal to 1 iso year
     */
    public function getIsoYear(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
    {
        return $this->isoYear($boundaryType);
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
        return $this == $interval->getStartDate() && !$interval->isStartIncluded();
    }

    /**
     * Tells whether the datepoint starts the interval.
     */
    public function isStarting(Period $interval): bool
    {
        return $interval->isStartedBy($this);
    }

    /**
     * Tells whether the datepoint is contained within the interval.
     */
    public function isDuring(Period $interval): bool
    {
        return $interval->contains($this);
    }

    /**
     * Tells whether the datepoint ends the interval.
     */
    public function isEnding(Period $interval): bool
    {
        return $interval->isEndedBy($this);
    }

    /**
     * Tells whether the datepoint borders on end the interval.
     */
    public function bordersOnEnd(Period $interval): bool
    {
        return $this == $interval->getEndDate() && !$interval->isEndIncluded();
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
        return $interval->isBefore($this);
    }
}
