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
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;

/**
 * A immutable value object class to manipulate Time interval.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
final class Period implements JsonSerializable
{
    private const ISO8601_FORMAT = 'Y-m-d\TH:i:s.u\Z';
    private const REGEXP_INTERVAL_NOTATION = '/^
            (?<startboundary>\[|\()
            (?<startdate>[^,\]\)\[\(]*)
            ,
            (?<enddate>[^,\]\)\[\(]*)
            (?<endboundary>\]|\))
        $/x';
    private const REGEXP_ISO_NOTATION = '/^(?<startdate>[^\/]*)\/(?<enddate>.*)$/';
    private const BOUNDARY_TYPE = [
        self::INCLUDE_START_EXCLUDE_END => 1,
        self::INCLUDE_ALL => 1,
        self::EXCLUDE_START_INCLUDE_END => 1,
        self::EXCLUDE_ALL => 1,
    ];

    public const INCLUDE_START_EXCLUDE_END = '[)';
    public const EXCLUDE_START_INCLUDE_END = '(]';
    public const EXCLUDE_ALL = '()';
    public const INCLUDE_ALL = '[]';

    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private string $boundaries;

    /**
     * @throws InvalidTimeRange If the instance can not be created
     */
    private function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate, string $boundaries)
    {
        if ($startDate > $endDate) {
            throw InvalidTimeRange::dueToDatepointMismatch();
        }

        if (!isset(self::BOUNDARY_TYPE[$boundaries])) {
            throw InvalidTimeRange::dueToUnknownBoundaries($boundaries, self::BOUNDARY_TYPE);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->boundaries = $boundaries;
    }

    /**************************************************
     * Named constructors
     **************************************************/

    /**
     * @param array{startDate:DateTimeImmutable, endDate:DateTimeImmutable, boundaries:string} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['startDate'], $properties['endDate'], $properties['boundaries']);
    }

    public static function fromIso8601(string $format, string $notation, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        if (1 !== preg_match(self::REGEXP_ISO_NOTATION, $notation, $found)) {
            throw InvalidTimeRange::dueToUnknownNotation($notation);
        }

        return self::fromDateString(
            $format,
            trim($found['startdate']),
            trim($found['enddate']),
            $boundaries
        );
    }

    public static function fromNotation(string $format, string $notation): self
    {
        if (1 !== preg_match(self::REGEXP_INTERVAL_NOTATION, $notation, $found)) {
            throw InvalidTimeRange::dueToUnknownNotation($notation);
        }

        return self::fromDateString(
            $format,
            trim($found['startdate']),
            trim($found['enddate']),
            $found['startboundary'].$found['endboundary']
        );
    }

    private static function fromDateString(string $format, string $startDateString, string $endDateString, string $boundaries): self
    {
        if (false === ($startDate = DateTimeImmutable::createFromFormat($format, $startDateString))) {
            throw InvalidTimeRange::dueToInvalidDateFormat($format, $startDateString);
        }

        if (false === ($endDate = DateTimeImmutable::createFromFormat($format, $endDateString))) {
            throw InvalidTimeRange::dueToInvalidDateFormat($format, $endDateString);
        }

        return new self($startDate, $endDate, $boundaries);
    }

    public static function fromDate(
        DatePoint|DateTimeInterface $startDate,
        DatePoint|DateTimeInterface $endDate,
        string $boundaries = self::INCLUDE_START_EXCLUDE_END
    ): self {
        return new self(self::filterDatepoint($startDate), self::filterDatepoint($endDate), $boundaries);
    }

    private static function filterDatepoint(DatePoint|DateTimeInterface $datepoint): DateTimeImmutable
    {
        return match (true) {
            $datepoint instanceof DatePoint => $datepoint->toDate(),
            $datepoint instanceof DateTimeImmutable => $datepoint,
            default => DateTimeImmutable::createFromInterface($datepoint),
        };
    }

    private static function filterDuration(Period|Duration|DateInterval $duration): DateInterval
    {
        return match (true) {
            $duration instanceof Period => $duration->dateInterval(),
            $duration instanceof Duration => $duration->toInterval(),
            default => $duration,
        };
    }

    /**
     * Creates new instance from a starting datepoint and a duration.
     *
     * @param DatePoint|DateTimeInterface  $startDate
     * @param Period|Duration|DateInterval $duration
     */
    public static function after(
        DatePoint|DateTimeInterface $startDate,
        Period|Duration|DateInterval $duration,
        string $boundaries = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $startDate = self::filterDatepoint($startDate);

        return new self($startDate, $startDate->add(self::filterDuration($duration)), $boundaries);
    }

    /**
     * Creates new instance where the given duration is simultaneously
     * subtracted from and added to the given datepoint.
     *
     * @param DatePoint|DateTimeInterface  $datepoint
     * @param Period|Duration|DateInterval $duration
     */
    public static function around(
        DatePoint|DateTimeInterface $datepoint,
        Period|Duration|DateInterval $duration,
        string $boundaries = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $datepoint = self::filterDatepoint($datepoint);
        $duration = self::filterDuration($duration);

        return new self($datepoint->sub($duration), $datepoint->add($duration), $boundaries);
    }

    /**
     * Creates new instance from a ending datepoint and a duration.
     *
     * @param DatePoint|DateTimeInterface  $endDate
     * @param Period|Duration|DateInterval $duration
     */
    public static function before(
        DatePoint|DateTimeInterface $endDate,
        Period|Duration|DateInterval $duration,
        string $boundaries = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $endDate = self::filterDatepoint($endDate);

        return new self($endDate->sub(self::filterDuration($duration)), $endDate, $boundaries);
    }

    public static function fromDatePeriod(DatePeriod $datePeriod, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $endDate = $datePeriod->getEndDate();
        if (null === $endDate) {
            throw InvalidTimeRange::dueToInvalidDatePeriod();
        }

        return new self(
            self::filterDatepoint($datePeriod->getStartDate()),
            self::filterDatepoint($endDate),
            $boundaries
        );
    }

    public static function fromYear(int $year, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')), $boundaries);
    }

    public static function fromIsoYear(int $year, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(
            (new DateTimeImmutable())->setTime(0, 0)->setISODate($year, 1),
            (new DateTimeImmutable())->setTime(0, 0)->setISODate($year + 1, 1),
            $boundaries
        );
    }

    public static function fromSemester(int $year, int $semester, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($semester - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P6M')), $boundaries);
    }

    public static function fromQuarter(int $year, int $quarter, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($quarter - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')), $boundaries);
    }

    public static function fromMonth(int $year, int $month, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')), $boundaries);
    }

    public static function fromIsoWeek(int $year, int $week, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setISODate($year, $week, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P7D')), $boundaries);
    }

    public static function fromDay(int $year, int $month, int $day, string $boundaries = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')), $boundaries);
    }

    /**************************************************
     * Basic getters
     **************************************************/

    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function boundaries(): string
    {
        return $this->boundaries;
    }

    public function timestampInterval(): int
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    public function dateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**************************************************
     * String representation
     **************************************************/

    /**
     * Returns the mathematical representation of an instance as a left close, right open interval.
     *
     * @see https://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals
     * @see https://php.net/manual/en/function.date.php
     * @see https://www.postgresql.org/docs/9.3/static/rangetypes.html
     *
     * @param string $format the format of the outputted date string
     */
    public function toNotation(string $format): string
    {
        return $this->boundaries[0]
            .$this->startDate->format($format)
            .', '
            .$this->endDate->format($format)
            .$this->boundaries[1];
    }

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     */
    public function toIso8601(string $format = self::ISO8601_FORMAT): string
    {
        $utc = new DateTimeZone('UTC');

        $startDate = $this->startDate->setTimezone($utc)->format($format);
        $endDate = $this->endDate->setTimezone($utc)->format($format);

        return $startDate.'/'.$endDate;
    }

    /**
     * Returns the JSON representation of an instance.
     *
     * Based on the JSON representation of dates as
     * returned by Javascript Date.toJSON() method.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toJSON
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toISOString
     *
     * @return array{startDate:string, endDate:string, startExcluded:bool, endExcluded:bool}
     */
    public function jsonSerialize()
    {
        $utc = new DateTimeZone('UTC');

        return [
            'startDate' => $this->startDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
            'endDate' => $this->endDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
            'startDateExcluded' => $this->isStartDateExcluded(),
            'endDateExcluded' => $this->isEndDateExcluded(),
        ];
    }

    /**************************************************
     * Boundary related methods
     **************************************************/

    /**
     * Tells whether the start datepoint is included in the boundary.
     */
    public function isStartDateIncluded(): bool
    {
        return '[' === $this->boundaries[0];
    }

    /**
     * Tells whether the start datepoint is excluded from the boundary.
     */
    public function isStartDateExcluded(): bool
    {
        return '(' === $this->boundaries[0];
    }

    /**
     * Tells whether the end datepoint is included in the boundary.
     */
    public function isEndDateIncluded(): bool
    {
        return ']' === $this->boundaries[1];
    }

    /**
     * Tells whether the end datepoint is excluded from the boundary.
     */
    public function isEndDateExcluded(): bool
    {
        return ')' === $this->boundaries[1];
    }

    /**************************************************
     * Duration comparison methods
     **************************************************/

    /**
     * Compares the instances against a duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current Interval is lesser than the submitted Interval object</li>
     * <li>  1 if the current Interval is greater than the submitted Interval object</li>
     * <li>  0 if both Interval objects have the same duration</li>
     * </ul>
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationCompare(Period|Duration|DateInterval $duration): int
    {
        return $this->startDate->add($this->dateInterval()) <=> $this->startDate->add(self::filterDuration($duration));
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationGreaterThan(Period|Duration|DateInterval $duration): bool
    {
        return 1 === $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is greater than or equal to the submitted one.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationGreaterThanOrEquals(Period|Duration|DateInterval $duration): bool
    {
        return 0 <= $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is equal to the submitted one.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationEquals(Period|Duration|DateInterval $duration): bool
    {
        return 0 === $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is greater than or equal to the submitted one.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationLessThanOrEquals(Period|Duration|DateInterval $duration): bool
    {
        return 0 >= $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function durationLessThan(Period|Duration|DateInterval $duration): bool
    {
        return -1 === $this->durationCompare($duration);
    }

    /**************************************************
     * Relation methods
     **************************************************/

    /**
     * Tells whether an instance is entirely before the specified index.
     *
     * The index can be a DateTimeInterface object or another Period object.
     *
     * [--------------------)
     *                          [--------------------)
     * @param Period|DatePoint|DateTimeInterface $timeSlot
     */
    public function isBefore(Period|DatePoint|DateTimeInterface $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->endDate < $timeSlot->startDate
                || ($this->endDate == $timeSlot->startDate && $this->boundaries[1] !== $timeSlot->boundaries[0]);
        }

        $datepoint = self::filterDatepoint($timeSlot);

        return $this->endDate < $datepoint
            || ($this->endDate == $datepoint && ')' === $this->boundaries[1]);
    }

    /**
     * Tells whether the current instance end date meets the interval start date.
     *
     * [--------------------)
     *                      [--------------------)
     */
    public function bordersOnStart(self $timeSlot): bool
    {
        return $this->endDate == $timeSlot->startDate
            && '][' !== $this->boundaries[1].$timeSlot->boundaries[0];
    }

    /**
     * Tells whether two intervals share the same start datepoint
     * and the same starting boundary type.
     *
     *    [----------)
     *    [--------------------)
     *
     * or
     *
     *    [--------------------)
     *    [---------)
     * @param Period|DatePoint|DateTimeInterface $timeSlot
     */
    public function isStartedBy(Period|DatePoint|DateTimeInterface $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->startDate == $timeSlot->startDate
                && $this->boundaries[0] === $timeSlot->boundaries[0];
        }

        return self::filterDatepoint($timeSlot) == $this->startDate && '[' === $this->boundaries[0];
    }

    /**
     * Tells whether an instance is fully contained in the specified interval.
     *
     *     [----------)
     * [--------------------)
     */
    public function isDuring(self $timeSlot): bool
    {
        return $timeSlot->containsInterval($this);
    }

    /**
     * Tells whether an instance fully contains the specified index.
     *
     * The index can be a DateTimeInterface object or another Period object.
     *
     * @param Period|DatePoint|DateTimeInterface $timeSlot
     */
    public function contains(Period|DatePoint|DateTimeInterface $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->containsInterval($timeSlot);
        }

        return $this->containsDatepoint(self::filterDatepoint($timeSlot), $this->boundaries);
    }

    /**
     * Tells whether an instance fully contains another instance.
     *
     * [--------------------)
     *     [----------)
     */
    private function containsInterval(self $period): bool
    {
        return match (true) {
            $this->startDate < $period->startDate && $this->endDate > $period->endDate
                => true,
            $this->startDate == $period->startDate && $this->endDate == $period->endDate
                => $this->boundaries === $period->boundaries || '[]' === $this->boundaries,
            $this->startDate == $period->startDate
                => ($this->boundaries[0] === $period->boundaries[0] || '[' === $this->boundaries[0])
                    && $this->containsDatepoint($this->startDate->add($period->dateInterval()), $this->boundaries),
            $this->endDate == $period->endDate
                => ($this->boundaries[1] === $period->boundaries[1] || ']' === $this->boundaries[1])
                    && $this->containsDatepoint($this->endDate->sub($period->dateInterval()), $this->boundaries),
            default
                => false,
        };
    }

    /**
     * Tells whether an instance contains a datepoint.
     *
     * [------|------------)
     */
    private function containsDatepoint(DateTimeInterface $datepoint, string $boundaries): bool
    {
        return match ($boundaries) {
            self::EXCLUDE_ALL => $datepoint > $this->startDate && $datepoint < $this->endDate,
            self::INCLUDE_ALL => $datepoint >= $this->startDate && $datepoint <= $this->endDate,
            self::EXCLUDE_START_INCLUDE_END => $datepoint > $this->startDate && $datepoint <= $this->endDate,
            default => $datepoint >= $this->startDate && $datepoint < $this->endDate,
        };
    }

    /**
     * Tells whether two intervals share the same datepoints.
     *
     * [--------------------)
     * [--------------------)
     */
    public function equals(mixed $timeSlot): bool
    {
        return $timeSlot instanceof self
            && $this->startDate == $timeSlot->startDate
            && $this->endDate == $timeSlot->endDate
            && $this->boundaries === $timeSlot->boundaries;
    }

    /**
     * Tells whether two intervals share the same end datepoint
     * and the same ending boundary type.
     *
     *              [----------)
     *    [--------------------)
     *
     * or
     *
     *    [--------------------)
     *               [---------)
     *
     * @param Period|DatePoint|DateTimeInterface $timeSlot
     */
    public function isEndedBy(Period|DatePoint|DateTimeInterface $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->endDate == $timeSlot->endDate
                && $this->boundaries[1] === $timeSlot->boundaries[1];
        }

        return self::filterDatepoint($timeSlot) == $this->endDate && ']' === $this->boundaries[1];
    }

    /**
     * Tells whether the current instance start date meets the interval end date.
     *
     *                      [--------------------)
     * [--------------------)
     */
    public function bordersOnEnd(self $timeSlot): bool
    {
        return $timeSlot->bordersOnStart($this);
    }

    /**
     * Tells whether an interval is entirely after the specified index.
     * The index can be a DateTimeInterface object or another Period object.
     *
     *                          [--------------------)
     * [--------------------)
     *
     * @param Period|DatePoint|DateTimeInterface $timeSlot
     */
    public function isAfter(Period|DatePoint|DateTimeInterface $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $timeSlot->isBefore($this);
        }

        $datepoint = self::filterDatepoint($timeSlot);

        return $this->startDate > $datepoint
            || ($this->startDate == $datepoint && '(' === $this->boundaries[0]);
    }

    /**
     * Tells whether two intervals abuts.
     *
     * [--------------------)
     *                      [--------------------)
     * or
     *                      [--------------------)
     * [--------------------)
     */
    public function abuts(self $timeSlot): bool
    {
        return $this->bordersOnStart($timeSlot) || $this->bordersOnEnd($timeSlot);
    }

    /**
     * Tells whether two intervals overlaps.
     *
     * [--------------------)
     *          [--------------------)
     */
    public function overlaps(self $timeSlot): bool
    {
        return !$this->abuts($timeSlot)
            && $this->startDate < $timeSlot->endDate
            && $this->endDate > $timeSlot->startDate;
    }

    /**************************************************
     * Manipulating instance duration
     **************************************************/

    /**
     * Returns the difference between two instances expressed in seconds.
     */
    public function timestampIntervalDiff(self $period): int
    {
        return $this->timestampInterval() - $period->timestampInterval();
    }

    /**
     * Returns the difference between two instances expressed with a DateInterval object.
     */
    public function dateIntervalDiff(self $period): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($period->dateInterval()));
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function toDatePeriod(Period|Duration|DateInterval $duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, self::filterDuration($duration), $this->endDate, $option);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from
     * the instance ending datepoint.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function toDatePeriodBackwards(Period|Duration|DateInterval $duration, int $option = 0): iterable
    {
        $duration = self::filterDuration($duration);
        $date = $this->endDate;
        if (DatePeriod::EXCLUDE_START_DATE === ($option & DatePeriod::EXCLUDE_START_DATE)) {
            $date = $this->endDate->sub($duration);
        }

        while ($date > $this->startDate) {
            yield $date;
            $date = $date->sub($duration);
        }
    }

    /**
     * Allows splitting an instance in smaller Period objects according to a given interval.
     *
     * The returned iterable Interval set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the starting datepoint of the parent object.</li>
     * <li>The last returned object MUST share the ending datepoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST start immediately after the previously returned object</li>
     * </ul>
     *
     * @param Period|Duration|DateInterval $duration
     *
     * @return iterable<Period>
     */
    public function split(Period|Duration|DateInterval $duration): iterable
    {
        $duration = self::filterDuration($duration);
        /** @var DateTimeImmutable $startDate */
        foreach ($this->toDatePeriod($duration) as $startDate) {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }

            yield new self($startDate, $endDate, $this->boundaries);
        }
    }

    /**
     * Allows splitting an instance in smaller Period objects according to a given interval.
     *
     * The returned iterable Period set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the ending datepoint of the parent object.</li>
     * <li>The last returned object MUST share the starting datepoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST end immediately before the previously returned object</li>
     * </ul>
     *
     * @param Period|Duration|DateInterval $duration
     *
     * @return iterable<Period>
     */
    public function splitBackwards(Period|Duration|DateInterval $duration): iterable
    {
        $endDate = $this->endDate;
        $duration = self::filterDuration($duration);
        do {
            $startDate = $endDate->sub($duration);
            if ($startDate < $this->startDate) {
                $startDate = $this->startDate;
            }
            yield new self($startDate, $endDate, $this->boundaries);

            $endDate = $startDate;
        } while ($endDate > $this->startDate);
    }

    /**************************************************
     * Manipulation instance endpoints and boundaries
     **************************************************/

    /**
     * Returns the computed intersection between two instances as a new instance.
     *
     * [--------------------)
     *          ∩
     *                 [----------)
     *          =
     *                 [----)
     *
     * @throws InvalidTimeRange If both objects do not overlaps
     */
    public function intersect(self $period): self
    {
        if (!$this->overlaps($period)) {
            throw InvalidTimeRange::dueToNonOverlappingPeriod();
        }

        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $boundaries = $this->boundaries;
        if ($period->startDate > $this->startDate) {
            $boundaries[0] = $period->boundaries[0];
            $startDate = $period->startDate;
        }

        if ($period->endDate < $this->endDate) {
            $boundaries[1] = $period->boundaries[1];
            $endDate = $period->endDate;
        }

        $intersect = new self($startDate, $endDate, $boundaries);
        if ($intersect->equals($this)) {
            return $this;
        }

        return $intersect;
    }

    /**
     * Returns the computed difference between two overlapping instances as
     * an array containing Period objects or the null value.
     *
     * The array will always contains 2 elements:
     *
     * <ul>
     * <li>an NULL filled array if both objects have the same datepoints</li>
     * <li>one Period object and NULL if both objects share one datepoint</li>
     * <li>two Period objects if both objects share no datepoint</li>
     * </ul>
     *
     * [--------------------)
     *          \
     *                [-----------)
     *          =
     * [--------------)  +  [-----)
     */
    public function diff(self $period): Sequence
    {
        if ($period->equals($this)) {
            return new Sequence();
        }

        $intersect = $this->intersect($period);
        $merge = $this->merge($period);
        if ($merge->startDate == $intersect->startDate) {
            $first = ')' === $intersect->boundaries[1] ? '[' : '(';
            $boundary = $first.$merge->boundaries[1];

            return new Sequence($merge->startingOn($intersect->endDate)->withBoundaries($boundary));
        }

        if ($merge->endDate == $intersect->endDate) {
            $last = '(' === $intersect->boundaries[0] ? ']' : ')';
            $boundary = $merge->boundaries[0].$last;

            return new Sequence($merge->endingOn($intersect->startDate)->withBoundaries($boundary));
        }

        $last = '(' === $intersect->boundaries[0] ? ']' : ')';
        $lastBoundary = $merge->boundaries[0].$last;

        $first = ')' === $intersect->boundaries[1] ? '[' : '(';
        $firstBoundary = $first.$merge->boundaries[1];

        return new Sequence(
            $merge->endingOn($intersect->startDate)->withBoundaries($lastBoundary),
            $merge->startingOn($intersect->endDate)->withBoundaries($firstBoundary),
        );
    }

    /**
     * Returns the difference set operation between two intervals as a Sequence.
     * The Sequence can contain from 0 to 2 Periods depending on the result of
     * the operation.
     *
     * [--------------------)
     *          -
     *                [-----------)
     *          =
     * [--------------)
     */
    public function subtract(self $period): Sequence
    {
        if (!$this->overlaps($period)) {
            return new Sequence($this);
        }

        return $this->diff($period)->filter(fn (Period $item): bool => $this->overlaps($item));
    }

    /**
     * Returns the computed gap between two instances as a new instance.
     *
     * [--------------------)
     *          +
     *                          [----------)
     *          =
     *                      [---)
     *
     * @throws InvalidTimeRange If both instance overlaps
     */
    public function gap(self $period): self
    {
        if ($this->overlaps($period)) {
            throw InvalidTimeRange::dueToNonOverlappingPeriod();
        }

        $boundaries = $this->isEndDateExcluded() ? '[' : '(';
        $boundaries .= $period->isStartDateExcluded() ? ']' : ')';
        if ($period->startDate > $this->startDate) {
            return new self($this->endDate, $period->startDate, $boundaries);
        }

        return new self($period->endDate, $this->startDate, $this->boundaries);
    }

    /**
     * Merges one or more instances to return a new instance.
     * The resulting instance represents the largest duration possible.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * [--------------------)
     *          +
     *                 [----------)
     *          =
     * [--------------------------)
     *
     *
     * @param Period ...$periods
     */
    public function merge(self ...$periods): self
    {
        $reducer = function (Period $carry, Period $period): Period {
            if ($carry->startDate > $period->startDate) {
                $carry = new self(
                    $period->startDate,
                    $carry->endDate,
                    $period->boundaries[0].$carry->boundaries[1]
                );
            }

            if ($carry->endDate < $period->endDate) {
                $carry = new self(
                    $carry->startDate,
                    $period->endDate,
                    $carry->boundaries[0].$period->boundaries[1]
                );
            }

            return $carry;
        };

        return array_reduce($periods, $reducer, $this);
    }

    /**************************************************
     * Mutation methods
     **************************************************/

    /**
     * Returns an instance with the specified starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     *
     * @param DatePoint|DateTimeInterface $startDate
     */
    public function startingOn(DatePoint|DateTimeInterface $startDate): self
    {
        $startDate = self::filterDatepoint($startDate);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate, $this->boundaries);
    }

    /**
     * Returns an instance with the specified ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     *
     * @param DatePoint|DateTimeInterface $endDate
     */
    public function endingOn(DatePoint|DateTimeInterface $endDate): self
    {
        $endDate = self::filterDatepoint($endDate);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate, $this->boundaries);
    }

    /**
     * Returns an instance with the specified boundary type.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the specified range type.
     */
    public function withBoundaries(string $boundaries): self
    {
        if ($boundaries === $this->boundaries) {
            return $this;
        }

        return new self($this->startDate, $this->endDate, $boundaries);
    }

    /**
     * Returns a new instance with a new ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     * @param Period|Duration|DateInterval $duration
     */
    public function withDurationAfterStart(Period|Duration|DateInterval $duration): self
    {
        return $this->endingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     * @param Period|Duration|DateInterval $duration
     */
    public function withDurationBeforeEnd(Period|Duration|DateInterval $duration): self
    {
        return $this->startingOn($this->endDate->sub(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function moveStartDate(Period|Duration|DateInterval $duration): self
    {
        return $this->startingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new ending datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function moveEndDate(Period|Duration|DateInterval $duration): self
    {
        return $this->endingOn($this->endDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function move(Period|Duration|DateInterval $duration): self
    {
        $duration = self::filterDuration($duration);
        $interval = new self($this->startDate->add($duration), $this->endDate->add($duration), $this->boundaries);
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * subtracted from the starting datepoint and added to the ending datepoint.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrunken.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * @param Period|Duration|DateInterval $duration
     */
    public function expand(Period|Duration|DateInterval $duration): self
    {
        $duration = self::filterDuration($duration);
        $interval = new self($this->startDate->sub($duration), $this->endDate->add($duration), $this->boundaries);
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a second interval.
     */
    public function snapToSecond(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->second()->startDate(),
            DatePoint::fromDate($this->endDate)->second()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a minute interval.
     */
    public function snapToMinute(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->minute()->startDate(),
            DatePoint::fromDate($this->endDate)->minute()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a hour interval.
     */
    public function snapToHour(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->hour()->startDate(),
            DatePoint::fromDate($this->endDate)->hour()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a day interval.
     */
    public function snapToDay(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->day()->startDate(),
            DatePoint::fromDate($this->endDate)->day()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a iso week interval.
     */
    public function snapToIsoWeek(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->isoWeek()->startDate(),
            DatePoint::fromDate($this->endDate)->isoWeek()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a month interval.
     */
    public function snapToMonth(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->month()->startDate(),
            DatePoint::fromDate($this->endDate)->month()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a quarter interval.
     */
    public function snapToQuarter(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->quarter()->startDate(),
            DatePoint::fromDate($this->endDate)->quarter()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a semeter interval.
     */
    public function snapToSemester(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->semester()->startDate(),
            DatePoint::fromDate($this->endDate)->semester()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a year interval.
     */
    public function snapToYear(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->year()->startDate(),
            DatePoint::fromDate($this->endDate)->year()->endDate(),
            $this->boundaries
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to a iso year interval.
     */
    public function snapToIsoYear(): self
    {
        return new self(
            DatePoint::fromDate($this->startDate)->isoYear()->startDate(),
            DatePoint::fromDate($this->endDate)->isoYear()->endDate(),
            $this->boundaries
        );
    }
}
