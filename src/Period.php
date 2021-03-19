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
    private string $boundaryType;

    /**
     * @throws InvalidTimeRange If the instance can not be created
     */
    private function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate, string $boundaryType)
    {
        if ($startDate > $endDate) {
            throw InvalidTimeRange::dueToDatepointMismatch();
        }

        if (!isset(self::BOUNDARY_TYPE[$boundaryType])) {
            throw InvalidTimeRange::dueToInvalidBoundaryType($boundaryType, self::BOUNDARY_TYPE);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->boundaryType = $boundaryType;
    }

    /**************************************************
     * Named constructors
     **************************************************/

    /**
     * Returns a DateTimeImmutable instance.
     * @param Datepoint|DateTimeInterface|string|int $datepoint
     */
    private static function filterDatepoint(Datepoint|DateTimeInterface|string|int $datepoint): DateTimeImmutable
    {
        return match (true) {
            $datepoint instanceof Datepoint => $datepoint->toDateTimeImmutable(),
            $datepoint instanceof DateTimeImmutable => $datepoint,
            $datepoint instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($datepoint),
            is_string($datepoint) => new DateTimeImmutable($datepoint),
            default => (new DateTimeImmutable())->setTimestamp($datepoint),
        };
    }

    /**
     * Returns a DateInterval instance.
     * @param Period|Duration|DateInterval|string|int $duration
     */
    private static function filterDuration(Period|Duration|DateInterval|string|int $duration): DateInterval
    {
        return match (true) {
            $duration instanceof self => $duration->dateInterval(),
            $duration instanceof Duration => $duration->toDateInterval(),
            $duration instanceof DateInterval => $duration,
            is_string($duration) => DateInterval::createFromDateString($duration),
            default => Duration::fromSeconds($duration)->toDateInterval(),
        };
    }

    /**
     * @inheritDoc
     */
    public static function __set_state(array $interval)
    {
        return new self($interval['startDate'], $interval['endDate'], $interval['boundaryType']);
    }

    /**
     * Creates new instance from a starting datepoint and a duration.
     * @param Datepoint|DateTimeInterface|string|int  $startDate
     * @param Period|Duration|DateInterval|string|int $duration
     */
    public static function after(
        Datepoint|DateTimeInterface|string|int $startDate,
        Period|Duration|DateInterval|string|int $duration,
        string $boundaryType = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $startDate = self::filterDatepoint($startDate);

        return new self($startDate, $startDate->add(self::filterDuration($duration)), $boundaryType);
    }

    /**
     * Creates new instance where the given duration is simultaneously
     * subtracted from and added to the given datepoint.
     *
     * @param Datepoint|DateTimeInterface|string|int  $datepoint
     * @param Period|Duration|DateInterval|string|int $duration
     */
    public static function around(
        Datepoint|DateTimeInterface|string|int $datepoint,
        Period|Duration|DateInterval|string|int $duration,
        string $boundaryType = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $datepoint = self::filterDatepoint($datepoint);
        $duration = self::filterDuration($duration);

        return new self($datepoint->sub($duration), $datepoint->add($duration), $boundaryType);
    }

    /**
     * Creates new instance from a ending datepoint and a duration.
     *
     * @param Datepoint|DateTimeInterface|string|int  $endDate
     * @param Period|Duration|DateInterval|string|int $duration
     */
    public static function before(
        Datepoint|DateTimeInterface|string|int $endDate,
        Period|Duration|DateInterval|string|int $duration,
        string $boundaryType = self::INCLUDE_START_EXCLUDE_END
    ): self {
        $endDate = self::filterDatepoint($endDate);

        return new self($endDate->sub(self::filterDuration($duration)), $endDate, $boundaryType);
    }

    public static function fromDatePeriod(DatePeriod $datePeriod, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $endDate = $datePeriod->getEndDate();
        if (null === $endDate) {
            throw InvalidTimeRange::dueToInvalidDatePeriod();
        }

        return new self(
            self::filterDatepoint($datePeriod->getStartDate()),
            self::filterDatepoint($endDate),
            $boundaryType
        );
    }

    public static function fromYear(int $year, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')), $boundaryType);
    }

    public static function fromIsoYear(int $year, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(
            (new DateTimeImmutable())->setISODate($year, 1)->setTime(0, 0),
            (new DateTimeImmutable())->setISODate(++$year, 1)->setTime(0, 0),
            $boundaryType
        );
    }

    public static function fromSemester(int $year, int $semester = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($semester - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P6M')), $boundaryType);
    }

    public static function fromQuarter(int $year, int $quarter = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($quarter - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')), $boundaryType);
    }

    public static function fromMonth(int $year, int $month = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')), $boundaryType);
    }

    public static function fromIsoWeek(int $year, int $week = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setISODate($year, $week, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P7D')), $boundaryType);
    }

    public static function fromDay(int $year, int $month = 1, int $day = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')), $boundaryType);
    }

    /**
     * Creates new instance for Datepoint.
     *
     * @param Datepoint|DateTimeInterface|string|int $startDate
     * @param Datepoint|DateTimeInterface|string|int $endDate
     */
    public static function fromDatepoint(
        Datepoint|DateTimeInterface|string|int $startDate,
        Datepoint|DateTimeInterface|string|int $endDate,
        string $boundaryType = self::INCLUDE_START_EXCLUDE_END
    ): self {
        return new self(self::filterDatepoint($startDate), self::filterDatepoint($endDate), $boundaryType);
    }

    public static function fromNotation(string $notation): self
    {
        if (1 !== preg_match(self::REGEXP_INTERVAL_NOTATION, $notation, $found)) {
            throw InvalidTimeRange::dueToUnknownNotation($notation);
        }

        return self::fromDatepoint(
            trim($found['startdate']),
            trim($found['enddate']),
            $found['startboundary'].$found['endboundary']
        );
    }

    /**************************************************
     * Basic getters
     **************************************************/

    /**
     * Returns the starting datepoint.
     */
    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Returns the ending datepoint.
     */
    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Returns the instance boundary type.
     */
    public function boundaryType(): string
    {
        return $this->boundaryType;
    }

    /**
     * Returns the instance duration as expressed in seconds.
     */
    public function timestampInterval(): float
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * Returns the instance duration as a DateInterval object.
     */
    public function dateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**************************************************
     * String representation
     **************************************************/

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     * @param ?string $format
     */
    public function toIso8601(?string $format = null): string
    {
        $utc = new DateTimeZone('UTC');
        $format = $format ?? self::ISO8601_FORMAT;

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
     * @return array<string>
     */
    public function jsonSerialize()
    {
        [$startDate, $endDate] = explode('/', $this->toIso8601(), 2);

        return ['startDate' => $startDate, 'endDate' => $endDate];
    }

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
        return $this->boundaryType[0]
            .$this->startDate->format($format)
            .', '
            .$this->endDate->format($format)
            .$this->boundaryType[1];
    }

    /**************************************************
     * Boundary related methods
     **************************************************/

    /**
     * Tells whether the start datepoint is included in the boundary.
     */
    public function isStartIncluded(): bool
    {
        return '[' === $this->boundaryType[0];
    }

    /**
     * Tells whether the start datepoint is excluded from the boundary.
     */
    public function isStartExcluded(): bool
    {
        return '(' === $this->boundaryType[0];
    }

    /**
     * Tells whether the end datepoint is included in the boundary.
     */
    public function isEndIncluded(): bool
    {
        return ']' === $this->boundaryType[1];
    }

    /**
     * Tells whether the end datepoint is excluded from the boundary.
     */
    public function isEndExcluded(): bool
    {
        return ')' === $this->boundaryType[1];
    }

    /**************************************************
     * Duration comparison methods
     **************************************************/

    /**
     * Compares two instances according to their duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current Interval is lesser than the submitted Interval object</li>
     * <li>  1 if the current Interval is greater than the submitted Interval object</li>
     * <li>  0 if both Interval objects have the same duration</li>
     * </ul>
     */
    public function durationCompare(self $interval): int
    {
        return $this->startDate->add($this->dateInterval())
            <=> $this->startDate->add($interval->dateInterval());
    }

    /**
     * Tells whether the current instance duration is equal to the submitted one.
     */
    public function durationEquals(self $interval): bool
    {
        return 0 === $this->durationCompare($interval);
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     */
    public function durationGreaterThan(self $interval): bool
    {
        return 1 === $this->durationCompare($interval);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     */
    public function durationLessThan(self $interval): bool
    {
        return -1 === $this->durationCompare($interval);
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
     *
     * @param self|Datepoint|DateTimeInterface|string|int $index a datepoint or a Period object
     */
    public function isBefore(self|Datepoint|DateTimeInterface|string|int $index): bool
    {
        if ($index instanceof self) {
            return $this->endDate < $index->startDate
                || ($this->endDate == $index->startDate && $this->boundaryType[1] !== $index->boundaryType[0]);
        }

        $datepoint = self::filterDatepoint($index);

        return $this->endDate < $datepoint
            || ($this->endDate == $datepoint && ')' === $this->boundaryType[1]);
    }

    /**
     * Tells whether the current instance end date meets the interval start date.
     *
     * [--------------------)
     *                      [--------------------)
     */
    public function bordersOnStart(self $interval): bool
    {
        return $this->endDate == $interval->startDate
            && '][' !== $this->boundaryType[1].$interval->boundaryType[0];
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
     *
     * @param self|Datepoint|DateTimeInterface|string|int $index a datepoint or a Period object
     */
    public function isStartedBy(self|Datepoint|DateTimeInterface|string|int $index): bool
    {
        if ($index instanceof self) {
            return $this->startDate == $index->startDate
                && $this->boundaryType[0] === $index->boundaryType[0];
        }

        $index = self::filterDatepoint($index);

        return $index == $this->startDate && '[' === $this->boundaryType[0];
    }

    /**
     * Tells whether an instance is fully contained in the specified interval.
     *
     *     [----------)
     * [--------------------)
     */
    public function isDuring(self $interval): bool
    {
        return $interval->containsInterval($this);
    }

    /**
     * Tells whether an instance fully contains the specified index.
     *
     * The index can be a DateTimeInterface object or another Period object.
     *
     * @param self|Datepoint|DateTimeInterface|string|int $index a datepoint or a Period object
     */
    public function contains(self|Datepoint|DateTimeInterface|string|int $index): bool
    {
        if ($index instanceof self) {
            return $this->containsInterval($index);
        }

        return $this->containsDatepoint(self::filterDatepoint($index), $this->boundaryType);
    }

    /**
     * Tells whether an instance fully contains another instance.
     *
     * [--------------------)
     *     [----------)
     */
    private function containsInterval(self $interval): bool
    {
        if ($this->startDate < $interval->startDate && $this->endDate > $interval->endDate) {
            return true;
        }

        if ($this->startDate == $interval->startDate && $this->endDate == $interval->endDate) {
            return $this->boundaryType === $interval->boundaryType || '[]' === $this->boundaryType;
        }

        if ($this->startDate == $interval->startDate) {
            return ($this->boundaryType[0] === $interval->boundaryType[0] || '[' === $this->boundaryType[0])
                && $this->containsDatepoint($this->startDate->add($interval->dateInterval()), $this->boundaryType);
        }

        if ($this->endDate == $interval->endDate) {
            return ($this->boundaryType[1] === $interval->boundaryType[1] || ']' === $this->boundaryType[1])
                && $this->containsDatepoint($this->endDate->sub($interval->dateInterval()), $this->boundaryType);
        }

        return false;
    }

    /**
     * Tells whether an instance contains a datepoint.
     *
     * [------|------------)
     */
    private function containsDatepoint(DateTimeInterface $datepoint, string $boundaryType): bool
    {
        return match ($boundaryType) {
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
    public function equals(self $interval): bool
    {
        return $this->startDate == $interval->startDate
            && $this->endDate == $interval->endDate
            && $this->boundaryType === $interval->boundaryType;
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
     * @param self|Datepoint|DateTimeInterface|string|int $index a datepoint or a Period object
     */
    public function isEndedBy(self|Datepoint|DateTimeInterface|string|int $index): bool
    {
        if ($index instanceof self) {
            return $this->endDate == $index->endDate
                && $this->boundaryType[1] === $index->boundaryType[1];
        }

        return self::filterDatepoint($index) == $this->endDate && ']' === $this->boundaryType[1];
    }

    /**
     * Tells whether the current instance start date meets the interval end date.
     *
     *                      [--------------------)
     * [--------------------)
     */
    public function bordersOnEnd(self $interval): bool
    {
        return $interval->bordersOnStart($this);
    }

    /**
     * Tells whether an interval is entirely after the specified index.
     * The index can be a DateTimeInterface object or another Period object.
     *
     *                          [--------------------)
     * [--------------------)
     *
     * @param self|Datepoint|DateTimeInterface|string|int $index a datepoint or a Period object
     */
    public function isAfter(self|Datepoint|DateTimeInterface|string|int $index): bool
    {
        if ($index instanceof self) {
            return $index->isBefore($this);
        }

        $datepoint = self::filterDatepoint($index);

        return $this->startDate > $datepoint
            || ($this->startDate == $datepoint && '(' === $this->boundaryType[0]);
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
    public function abuts(self $interval): bool
    {
        return $this->bordersOnStart($interval) || $this->bordersOnEnd($interval);
    }

    /**
     * Tells whether two intervals overlaps.
     *
     * [--------------------)
     *          [--------------------)
     */
    public function overlaps(self $interval): bool
    {
        return !$this->abuts($interval)
            && $this->startDate < $interval->endDate
            && $this->endDate > $interval->startDate;
    }

    /**************************************************
     * Manipulating instance duration
     **************************************************/

    /**
     * Returns the difference between two instances expressed in seconds.
     */
    public function timestampIntervalDiff(self $interval): float
    {
        return $this->timestampInterval() - $interval->timestampInterval();
    }

    /**
     * Returns the difference between two instances expressed with a DateInterval object.
     */
    public function dateIntervalDiff(self $interval): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($interval->dateInterval()));
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     *
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function toDatePeriod(Period|Duration|DateInterval|string|int $duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, self::filterDuration($duration), $this->endDate, $option);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from
     * the instance ending datepoint.
     *
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function toDatePeriodBackwards(Period|Duration|DateInterval|string|int $duration, int $option = 0): iterable
    {
        $duration = self::filterDuration($duration);
        $date = $this->endDate;
        if ((bool) ($option & DatePeriod::EXCLUDE_START_DATE)) {
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
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     *
     * @return iterable<Period>
     */
    public function split(Period|Duration|DateInterval|string|int $duration): iterable
    {
        $duration = self::filterDuration($duration);
        /** @var DateTimeImmutable $startDate */
        foreach ($this->toDatePeriod($duration) as $startDate) {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }

            yield new self($startDate, $endDate, $this->boundaryType);
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
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     *
     * @return iterable<Period>
     */
    public function splitBackwards(Period|Duration|DateInterval|string|int $duration): iterable
    {
        $endDate = $this->endDate;
        $duration = self::filterDuration($duration);
        do {
            $startDate = $endDate->sub($duration);
            if ($startDate < $this->startDate) {
                $startDate = $this->startDate;
            }
            yield new self($startDate, $endDate, $this->boundaryType);

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
    public function intersect(self $interval): self
    {
        if (!$this->overlaps($interval)) {
            throw InvalidTimeRange::dueToNonOverlappingPeriod();
        }

        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $boundaryType = $this->boundaryType;
        if ($interval->startDate > $this->startDate) {
            $boundaryType[0] = $interval->boundaryType[0];
            $startDate = $interval->startDate;
        }

        if ($interval->endDate < $this->endDate) {
            $boundaryType[1] = $interval->boundaryType[1];
            $endDate = $interval->endDate;
        }

        $intersect = new self($startDate, $endDate, $boundaryType);
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
    public function diff(self $interval): Sequence
    {
        if ($interval->equals($this)) {
            return new Sequence();
        }

        $intersect = $this->intersect($interval);
        $merge = $this->merge($interval);
        if ($merge->startDate == $intersect->startDate) {
            $first = ')' === $intersect->boundaryType[1] ? '[' : '(';
            $boundary = $first.$merge->boundaryType[1];

            return new Sequence($merge->startingOn($intersect->endDate)->withBoundaryType($boundary));
        }

        if ($merge->endDate == $intersect->endDate) {
            $last = '(' === $intersect->boundaryType[0] ? ']' : ')';
            $boundary = $merge->boundaryType[0].$last;

            return new Sequence($merge->endingOn($intersect->startDate)->withBoundaryType($boundary));
        }

        $last = '(' === $intersect->boundaryType[0] ? ']' : ')';
        $lastBoundary = $merge->boundaryType[0].$last;

        $first = ')' === $intersect->boundaryType[1] ? '[' : '(';
        $firstBoundary = $first.$merge->boundaryType[1];

        return new Sequence(
            $merge->endingOn($intersect->startDate)->withBoundaryType($lastBoundary),
            $merge->startingOn($intersect->endDate)->withBoundaryType($firstBoundary),
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
    public function subtract(self $interval): Sequence
    {
        if (!$this->overlaps($interval)) {
            return new Sequence($this);
        }

        return $this->diff($interval)->filter(fn (Period $item): bool => $this->overlaps($item));
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
    public function gap(self $interval): self
    {
        if ($this->overlaps($interval)) {
            throw InvalidTimeRange::dueToNonOverlappingPeriod();
        }

        $boundaryType = $this->isEndExcluded() ? '[' : '(';
        $boundaryType .= $interval->isStartExcluded() ? ']' : ')';
        if ($interval->startDate > $this->startDate) {
            return new self($this->endDate, $interval->startDate, $boundaryType);
        }

        return new self($interval->endDate, $this->startDate, $this->boundaryType);
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
     * @param Period ...$intervals
     */
    public function merge(self ...$intervals): self
    {
        $carry = $this;
        foreach ($intervals as $period) {
            if ($carry->startDate > $period->startDate) {
                $carry = new self(
                    $period->startDate,
                    $carry->endDate,
                    $period->boundaryType[0].$carry->boundaryType[1]
                );
            }

            if ($carry->endDate < $period->endDate) {
                $carry = new self(
                    $carry->startDate,
                    $period->endDate,
                    $carry->boundaryType[0].$period->boundaryType[1]
                );
            }
        }

        return $carry;
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
     * @param Datepoint|DateTimeInterface|string|int $startDate the new starting datepoint
     */
    public function startingOn(Datepoint|DateTimeInterface|string|int $startDate): self
    {
        $startDate = self::filterDatepoint($startDate);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate, $this->boundaryType);
    }

    /**
     * Returns an instance with the specified ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     *
     * @param Datepoint|DateTimeInterface|string|int $endDate the new ending datepoint
     */
    public function endingOn(Datepoint|DateTimeInterface|string|int $endDate): self
    {
        $endDate = self::filterDatepoint($endDate);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate, $this->boundaryType);
    }

    /**
     * Returns an instance with the specified boundary type.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the specified range type.
     */
    public function withBoundaryType(string $boundaryType): self
    {
        if ($boundaryType === $this->boundaryType) {
            return $this;
        }

        return new self($this->startDate, $this->endDate, $boundaryType);
    }

    /**
     * Returns a new instance with a new ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     *
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function withDurationAfterStart(Period|Duration|DateInterval|string|int $duration): self
    {
        return $this->endingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     *
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function withDurationBeforeEnd(Period|Duration|DateInterval|string|int $duration): self
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
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function moveStartDate(Period|Duration|DateInterval|string|int $duration): self
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
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function moveEndDate(Period|Duration|DateInterval|string|int $duration): self
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
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function move(Period|Duration|DateInterval|string|int $duration): self
    {
        $duration = self::filterDuration($duration);
        $interval = new self($this->startDate->add($duration), $this->endDate->add($duration), $this->boundaryType);
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * subtracted from the starting datepoint and added to the ending datepoint.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrinked.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * @param Period|Duration|DateInterval|string|int $duration a Duration
     */
    public function expand(Period|Duration|DateInterval|string|int $duration): self
    {
        $duration = self::filterDuration($duration);
        $interval = new self($this->startDate->sub($duration), $this->endDate->add($duration), $this->boundaryType);
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }
}
