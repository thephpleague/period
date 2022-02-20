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
use function array_filter;
use function array_keys;
use function implode;
use function sprintf;

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

    /** @var DateTimeImmutable */
    private $startDate;

    /** @var DateTimeImmutable */
    private $endDate;

    /** @var string */
    private $boundaryType;

    /**
     * Creates a new instance.
     *
     * @param Datepoint|DateTimeInterface|int|string $startDate the starting datepoint
     * @param Datepoint|DateTimeInterface|int|string $endDate   the ending datepoint
     *
     * @throws Exception If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate, string $boundaryType = self::INCLUDE_START_EXCLUDE_END)
    {
        $startDate = self::filterDatepoint($startDate);
        $endDate = self::filterDatepoint($endDate);
        if ($startDate > $endDate) {
            throw new Exception('The ending datepoint must be greater or equal to the starting datepoint');
        }

        if (!isset(self::BOUNDARY_TYPE[$boundaryType])) {
            throw new Exception(sprintf(
                'The boundary type `%s` is invalid. The only valid values are %s',
                $boundaryType,
                '`'.implode('`, `', array_keys(self::BOUNDARY_TYPE)).'`'
            ));
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->boundaryType = $boundaryType;
    }

    /**
     * Returns a DateTimeImmutable instance.
     *
     * @param Datepoint|DateTimeInterface|int|string $datepoint a Datepoint
     */
    private static function filterDatepoint($datepoint): DateTimeImmutable
    {
        if ($datepoint instanceof DateTimeImmutable) {
            return $datepoint;
        }

        if ($datepoint instanceof DateTimeInterface) {
            return new DateTimeImmutable($datepoint->format('Y-m-d H:i:s.u'), $datepoint->getTimezone());
        }

        $timestamp = $datepoint;
        if (is_int($timestamp) || false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT))) {
            return new DateTimeImmutable('@'.$timestamp);
        }

        return new DateTimeImmutable($datepoint);
    }

    /**
     * Returns a DateInterval instance.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    private static function filterDuration($duration): DateInterval
    {
        if ($duration instanceof DateInterval) {
            return $duration;
        }

        if ($duration instanceof self) {
            return $duration->dateInterval();
        }

        return Duration::create($duration);
    }

    /**************************************************
     * Named constructors
     **************************************************/

    /**
     * @inheritDoc
     */
    public static function __set_state(array $interval)
    {
        return new self(
            $interval['startDate'],
            $interval['endDate'],
            $interval['boundaryType'] ?? self::INCLUDE_START_EXCLUDE_END
        );
    }

    /**
     * Creates new instance from a starting date endpoint and a duration.
     *
     * @param Datepoint|DateTimeInterface|int|string $startDate the starting date endpoint
     * @param DateInterval|Duration|string|int       $duration  a Duration
     */
    public static function after($startDate, $duration, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = self::filterDatepoint($startDate);

        return new self($startDate, $startDate->add(self::filterDuration($duration)), $boundaryType);
    }

    /**
     * Creates new instance from a ending date endpoint and a duration.
     *
     * @param Datepoint|DateTimeInterface|int|string $endDate  the ending date endpoint
     * @param DateInterval|Duration|string|int       $duration a Duration
     */
    public static function before($endDate, $duration, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $endDate = self::filterDatepoint($endDate);

        return new self($endDate->sub(self::filterDuration($duration)), $endDate, $boundaryType);
    }

    /**
     * Creates new instance where the given duration is simultaneously
     * subtracted from and added to the date endpoint.
     *
     * @param Datepoint|DateTimeInterface|int|string $datepoint a Date endpoint
     * @param DateInterval|Duration|string|int       $duration  a Duration
     */
    public static function around($datepoint, $duration, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $datepoint = self::filterDatepoint($datepoint);
        $duration = self::filterDuration($duration);

        return new self($datepoint->sub($duration), $datepoint->add($duration), $boundaryType);
    }

    /**
     * Creates new instance from a DatePeriod.
     */
    public static function fromDatePeriod(DatePeriod $datePeriod, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self($datePeriod->getStartDate(), $datePeriod->getEndDate(), $boundaryType);
    }

    /**
     * Creates new instance for a specific year.
     */
    public static function fromYear(int $year, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')), $boundaryType);
    }

    /**
     * Creates new instance for a specific ISO year.
     */
    public static function fromIsoYear(int $year, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(
            (new DateTimeImmutable())->setISODate($year, 1)->setTime(0, 0),
            (new DateTimeImmutable())->setISODate(++$year, 1)->setTime(0, 0),
            $boundaryType
        );
    }

    /**
     * Creates new instance for a specific year and semester.
     */
    public static function fromSemester(int $year, int $semester = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($semester - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P6M')), $boundaryType);
    }

    /**
     * Creates new instance for a specific year and quarter.
     */
    public static function fromQuarter(int $year, int $quarter = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $month = (($quarter - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')), $boundaryType);
    }

    /**
     * Creates new instance for a specific year and month.
     */
    public static function fromMonth(int $year, int $month = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')), $boundaryType);
    }

    /**
     * Creates new instance for a specific ISO8601 week.
     */
    public static function fromIsoWeek(int $year, int $week = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setISODate($year, $week, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P7D')), $boundaryType);
    }

    /**
     * Creates new instance for a specific year, month and day.
     */
    public static function fromDay(int $year, int $month = 1, int $day = 1, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')), $boundaryType);
    }

    /**
     * Creates new instance for Datepoint.
     */
    public static function fromDatepoint(DateTimeInterface $startDate, DateTimeInterface $endDate, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self($startDate, $endDate, $boundaryType);
    }

    /**************************************************
     * Basic getters
     **************************************************/

    /**
     * Returns the starting date endpoint.
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Returns the ending date endpoint.
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Returns the instance boundary type.
     */
    public function getBoundaryType(): string
    {
        return $this->boundaryType;
    }

    /**
     * Returns the instance duration as expressed in seconds.
     */
    public function timeDuration(): float
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::timeDuration()
     *
     * Returns the instance duration as expressed in seconds.
     */
    public function getTimestampInterval(): float
    {
        return $this->timeDuration();
    }

    /**
     * Returns the instance duration as a DateInterval object.
     */
    public function dateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::dateInterval()
     *
     * Returns the instance duration as a DateInterval object.
     */
    public function getDateInterval(): DateInterval
    {
        return $this->dateInterval();
    }

    /**************************************************
     * String representation
     **************************************************/

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * @deprecated 4.10.0 This method will be removed in the next major point release
     * @see ::toIso8601()
     */
    public function __toString()
    {
        return $this->toIso8601();
    }

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
    public function jsonSerialize(): array
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
    public function toIso80000(string $format): string
    {
        return $this->boundaryType[0]
            .$this->startDate->format($format)
            .', '
            .$this->endDate->format($format)
            .$this->boundaryType[1];
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::toIso80000()
     *
     * @param string $format the format of the outputted date string
     *
     * Returns the mathematical representation of an instance as a left close, right open interval.
     *
     * @see https://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals
     * @see https://php.net/manual/en/function.date.php
     * @see https://www.postgresql.org/docs/9.3/static/rangetypes.html
     */
    public function format(string $format): string
    {
        return $this->toIso80000($format);
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
     * @param Period|Datepoint|\DateTimeInterface|int|string $index a datepoint or a Period object
     */
    public function isBefore($index): bool
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
     * @param Period|Datepoint|\DateTimeInterface|int|string $index a datepoint or a Period object
     */
    public function isStartedBy($index): bool
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
     * @param Period|Datepoint|\DateTimeInterface|int|string $index a datepoint or a Period object
     */
    public function contains($index): bool
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
                && $this->containsDatepoint($this->startDate->add($interval->getDateInterval()), $this->boundaryType);
        }

        if ($this->endDate == $interval->endDate) {
            return ($this->boundaryType[1] === $interval->boundaryType[1] || ']' === $this->boundaryType[1])
                && $this->containsDatepoint($this->endDate->sub($interval->getDateInterval()), $this->boundaryType);
        }

        return false;
    }

    /**
     * Tells whether an instance contains a date endpoint.
     *
     * [------|------------)
     */
    private function containsDatepoint(DateTimeInterface $datepoint, string $boundaryType): bool
    {
        switch ($boundaryType) {
            case self::EXCLUDE_ALL:
                return $datepoint > $this->startDate && $datepoint < $this->endDate;
            case self::INCLUDE_ALL:
                return $datepoint >= $this->startDate && $datepoint <= $this->endDate;
            case self::EXCLUDE_START_INCLUDE_END:
                return $datepoint > $this->startDate && $datepoint <= $this->endDate;
            case self::INCLUDE_START_EXCLUDE_END:
            default:
                return $datepoint >= $this->startDate && $datepoint < $this->endDate;
        }
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
     * @param Period|Datepoint|\DateTimeInterface|int|string $index a datepoint or a Period object
     */
    public function isEndedBy($index): bool
    {
        if ($index instanceof self) {
            return $this->endDate == $index->endDate
                && $this->boundaryType[1] === $index->boundaryType[1];
        }

        $index = self::filterDatepoint($index);

        return $index == $this->endDate && ']' === $this->boundaryType[1];
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
     * @param Period|Datepoint|\DateTimeInterface|int|string $index a datepoint or a Period object
     */
    public function isAfter($index): bool
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
    public function timeDurationDiff(self $interval): float
    {
        return $this->timeDuration() - $interval->timeDuration();
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::timeDurationDiff()
     *
     * Returns the difference between two instances expressed in seconds.
     */
    public function timestampIntervalDiff(self $interval): float
    {
        return $this->timeDurationDiff($interval);
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
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function dateRangeForward($duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, self::filterDuration($duration), $this->endDate, $option);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::dateRangeForward()
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     *
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     */
    public function getDatePeriod($duration, int $option = 0): DatePeriod
    {
        return $this->dateRangeForward($duration, $option);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from
     * the instance ending date endpoint.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function dateRangeBackwards($duration, int $option = 0): iterable
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
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::dateRangeBackwards()
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     *
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from
     * the instance ending date endpoint.
     */
    public function getDatePeriodBackwards($duration, int $option = 0): iterable
    {
        return $this->dateRangeBackwards($duration, $option);
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
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     *
     * @return iterable<Period>
     */
    public function splitForward($duration): iterable
    {
        $duration = self::filterDuration($duration);
        /** @var DateTimeImmutable $startDate */
        foreach ($this->dateRangeForward($duration) as $startDate) {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }

            yield new self($startDate, $endDate, $this->boundaryType);
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @see Period::splitForward()
     *
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
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     *
     * @return iterable<Period>
     */
    public function split($duration): iterable
    {
        return $this->splitForward($duration);
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
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     *
     * @return iterable<Period>
     */
    public function splitBackwards($duration): iterable
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
     * Manipulation instance endpoints and bounds
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
     * @throws Exception If both objects do not overlaps
     */
    public function intersect(self $interval): self
    {
        if (!$this->overlaps($interval)) {
            throw new Exception('Both '.self::class.' objects should overlaps');
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
     *
     * @return array<null|Period>
     */
    public function diff(self $interval): array
    {
        if ($interval->equals($this)) {
            return [null, null];
        }

        $intersect = $this->intersect($interval);
        $merge = $this->merge($interval);
        if ($merge->startDate == $intersect->startDate) {
            $first = ')' === $intersect->boundaryType[1] ? '[' : '(';
            $boundary = $first.$merge->boundaryType[1];

            return [$merge->startingOn($intersect->endDate)->boundedBy($boundary), null];
        }

        if ($merge->endDate == $intersect->endDate) {
            $last = '(' === $intersect->boundaryType[0] ? ']' : ')';
            $boundary = $merge->boundaryType[0].$last;

            return [$merge->endingOn($intersect->startDate)->boundedBy($boundary), null];
        }

        $last = '(' === $intersect->boundaryType[0] ? ']' : ')';
        $lastBoundary = $merge->boundaryType[0].$last;

        $first = ')' === $intersect->boundaryType[1] ? '[' : '(';
        $firstBoundary = $first.$merge->boundaryType[1];

        return [
            $merge->endingOn($intersect->startDate)->boundedBy($lastBoundary),
            $merge->startingOn($intersect->endDate)->boundedBy($firstBoundary),
        ];
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.9.0 This method will be removed in the next major point release
     * @see Period::subtract
     */
    public function substract(self $interval): Sequence
    {
        return $this->subtract($interval);
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

        $filter = function ($item): bool {
            return null !== $item && $this->overlaps($item);
        };

        return new Sequence(...array_filter($this->diff($interval), $filter));
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
     * @throws Exception If both instance overlaps
     */
    public function gap(self $interval): self
    {
        if ($this->overlaps($interval)) {
            throw new Exception('Both '.self::class.' objects must not overlaps');
        }

        $bounds = $this->isEndIncluded() ? '(' : '[';
        $bounds .= $interval->isStartIncluded() ? ')' : ']';
        if ($interval->startDate > $this->startDate) {
            return new self($this->endDate, $interval->startDate, $bounds);
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
     * Returns an instance with the specified starting date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     *
     * @param Datepoint|DateTimeInterface|int|string $startDate the new starting date endpoint
     */
    public function startingOn($startDate): self
    {
        $startDate = self::filterDatepoint($startDate);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate, $this->boundaryType);
    }

    /**
     * Returns an instance with the specified ending date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     *
     * @param Datepoint|DateTimeInterface|int|string $endDate the new ending date endpoint
     */
    public function endingOn($endDate): self
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
    public function boundedBy(string $bounds): self
    {
        if ($bounds === $this->boundaryType) {
            return $this;
        }

        return new self($this->startDate, $this->endDate, $bounds);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Period::boundedBy()
     *
     * Returns an instance with the specified boundary type.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the specified range type.
     */
    public function withBoundaryType(string $boundaryType): self
    {
        return $this->boundedBy($boundaryType);
    }

    /**
     * Returns a new instance with a new ending date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function withDurationAfterStart($duration): self
    {
        return $this->endingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function withDurationBeforeEnd($duration): self
    {
        return $this->startingOn($this->endDate->sub(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function moveStartDate($duration): self
    {
        return $this->startingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new ending datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function moveEndDate($duration): self
    {
        return $this->endingOn($this->endDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance where the date endpoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function move($duration): self
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
     * subtracted from the starting date endpoint and added to the ending date endpoint.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrinked.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     *
     * @param Period|DateInterval|Duration|string|int $duration a Duration
     */
    public function expand($duration): self
    {
        $duration = self::filterDuration($duration);
        $interval = new self($this->startDate->sub($duration), $this->endDate->add($duration), $this->boundaryType);
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }
}
