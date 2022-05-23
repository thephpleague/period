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
use Exception;
use Generator;
use JsonSerializable;

/**
 * An immutable value object class to manipulate DateTimeInterface interval.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
final class Period implements JsonSerializable
{
    /**
     * @throws InvalidInterval If the instance can not be created
     */
    private function __construct(
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly Bounds $bounds
    ) {
        if ($this->startDate > $this->endDate) {
            throw InvalidInterval::dueToEndPointMismatch();
        }
    }

    /**************************************************
     * Named constructors
     **************************************************/

    /**
     * @param array{startDate:DateTimeImmutable, endDate:DateTimeImmutable, bounds:Bounds} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['startDate'], $properties['endDate'], $properties['bounds']);
    }

    /**
     * @throws InvalidInterval If the notation is not supported or not known
     */
    public static function fromIso8601(string $format, string $notation, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        if (1 !== preg_match('/^(?<start>[^\/]*)\/(?<end>.*)$/', $notation, $found)) {
            throw InvalidInterval::dueToUnknownNotation('ISO-8601', $notation);
        }

        $start = trim($found['start']);
        $end = trim($found['end']);

        $findDuration = static function (string $duration): Duration|null {
            if (1 !== preg_match('/^P([YMDTHS\d.]+)$/', $duration)) {
                return null;
            }

            try {
                return Duration::fromIsoString($duration);
            } catch (Exception) {
                return null;
            }
        };

        return match (true) {
            null !== ($duration = $findDuration($start)) => self::before(DatePoint::fromFormat($format, $end), $duration, $bounds),
            null !== ($duration = $findDuration($end)) => self::after(DatePoint::fromFormat($format, $start), $duration, $bounds),
            default => self::fromDateString($format, $start, self::resolveIso8601Date($start, $end), $bounds),
        };
    }

    private static function resolveIso8601Date(string $startDate, string $endDate): string
    {
        /** @var array<string> $startDateComponents */
        $startDateComponents = preg_split('/\D/', $startDate);
        /** @var array<string> $endDateComponents */
        $endDateComponents = preg_split('/\D/', $endDate);

        $componentDiff = count($startDateComponents) - count($endDateComponents);
        if (0 > $componentDiff) {
            throw InvalidInterval::dueToInvalidRelativeDateFormat($endDate, $startDate);
        }

        if (0 === $componentDiff) {
            return $endDate;
        }

        /** @var array<string> $startDateDelimiters */
        $startDateDelimiters = preg_split('/\d+/', $startDate);
        /** @var array<string> $endDateDelimiters */
        $endDateDelimiters = preg_split('/\d+/', $endDate);
        $res = array_slice($startDateDelimiters, count($startDateDelimiters) - count($endDateDelimiters));
        $res[0] = '';
        if ($res !== $endDateDelimiters) {
            throw InvalidInterval::dueToInvalidRelativeDateFormat($endDate, $startDate);
        }

        $endDateExtendedComponents = array_merge(array_slice($startDateComponents, 0, $componentDiff), $endDateComponents);

        $endDateExtended = '';
        foreach ($startDateDelimiters as $offset => $char) {
            $endDateExtended .= $char.($endDateExtendedComponents[$offset] ?? '');
        }

        return $endDateExtended;
    }

    /**
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     * @throws InvalidInterval If the notation is not supported or not known
     */
    public static function fromBourbaki(string $format, string $notation): self
    {
        $interval = Bounds::parseBourbaki($notation);

        return self::fromDateString($format, $interval['start'], $interval['end'], $interval['bounds']);
    }

    /**
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     * @throws InvalidInterval If the notation is not supported or not known
     */
    public static function fromIso80000(string $format, string $notation): self
    {
        $interval = Bounds::parseIso80000($notation);

        return self::fromDateString($format, $interval['start'], $interval['end'], $interval['bounds']);
    }

    /**
     * @throws InvalidInterval If format can not be resolved
     */
    private static function fromDateString(string $format, string $startDate, string $endDate, Bounds $bounds): self
    {
        return new self(
            self::filterDatePoint(DatePoint::fromFormat($format, $startDate)),
            self::filterDatePoint(DatePoint::fromFormat($format, $endDate)),
            $bounds
        );
    }

    /**
     * @throws Exception
     */
    public static function fromDate(
        DatePoint|DateTimeInterface|string $startDate,
        DatePoint|DateTimeInterface|string $endDate,
        Bounds $bounds = Bounds::IncludeStartExcludeEnd
    ): self {
        return new self(self::filterDatePoint($startDate), self::filterDatePoint($endDate), $bounds);
    }

    /**
     * @throws Exception
     */
    private static function filterDatePoint(DatePoint|DateTimeInterface|string $datePoint): DateTimeImmutable
    {
        return match (true) {
            $datePoint instanceof DatePoint => $datePoint->date,
            $datePoint instanceof DateTimeImmutable => $datePoint,
            $datePoint instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($datePoint),
            default => DatePoint::fromDateString($datePoint)->date,
        };
    }

    private static function filterDuration(Period|Duration|DateInterval|string $duration): DateInterval
    {
        return match (true) {
            $duration instanceof Duration => $duration->dateInterval,
            $duration instanceof Period => $duration->dateInterval(),
            $duration instanceof DateInterval => $duration,
            default => Duration::fromDateString($duration)->dateInterval,
        };
    }

    /**
     * Creates new instance from a starting timestamp endpoint and ending timestamp.
     */
    public static function fromTimestamp(
        int $startDate,
        int $endDate,
        Bounds $bounds = Bounds::IncludeStartExcludeEnd
    ): self {
        return new self(
            DatePoint::fromTimestamp($startDate)->date,
            DatePoint::fromTimestamp($endDate)->date,
            $bounds
        );
    }

    /**
     * Creates new instance from a starting date endpoint and a duration.
     */
    public static function after(
        DatePoint|DateTimeInterface|string $startDate,
        Period|Duration|DateInterval|string $duration,
        Bounds $bounds = Bounds::IncludeStartExcludeEnd
    ): self {
        $startDate = self::filterDatePoint($startDate);

        return new self($startDate, $startDate->add(self::filterDuration($duration)), $bounds);
    }

    /**
     * Creates new instance where the given duration is simultaneously
     * subtracted from and added to the given date endpoint.
     */
    public static function around(
        DatePoint|DateTimeInterface|string $midpoint,
        Period|Duration|DateInterval|string $duration,
        Bounds $bounds = Bounds::IncludeStartExcludeEnd
    ): self {
        $midpoint = self::filterDatePoint($midpoint);
        $duration = self::filterDuration($duration);

        return new self($midpoint->sub($duration), $midpoint->add($duration), $bounds);
    }

    /**
     * Creates new instance from an ending date endpoint and a duration.
     */
    public static function before(
        DatePoint|DateTimeInterface|string $endDate,
        Period|Duration|DateInterval|string $duration,
        Bounds $bounds = Bounds::IncludeStartExcludeEnd
    ): self {
        $endDate = self::filterDatePoint($endDate);

        return new self($endDate->sub(self::filterDuration($duration)), $endDate, $bounds);
    }

    /**
     * @throws InvalidInterval If no instance can be generated from a DatePeriod object
     */
    public static function fromDateRange(DatePeriod $dateRange, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $endDate = $dateRange->getEndDate();
        if (null === $endDate) {
            throw InvalidInterval::dueToInvalidDatePeriod();
        }

        return new self(
            self::filterDatePoint($dateRange->getStartDate()),
            self::filterDatePoint($endDate),
            $bounds
        );
    }

    public static function fromYear(int $year, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')), $bounds);
    }

    public static function fromIsoYear(int $year, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $today = (new DateTimeImmutable())->setTime(0, 0);

        return new self($today->setISODate($year, 1), $today->setISODate($year + 1, 1), $bounds);
    }

    public static function fromSemester(int $year, int $semester, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $month = (($semester - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P6M')), $bounds);
    }

    public static function fromQuarter(int $year, int $quarter, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $month = (($quarter - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')), $bounds);
    }

    public static function fromMonth(int $year, int $month, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')), $bounds);
    }

    public static function fromIsoWeek(int $year, int $week, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $startDate = (new DateTimeImmutable())->setISODate($year, $week)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P7D')), $bounds);
    }

    public static function fromDay(int $year, int $month, int $day, Bounds $bounds = Bounds::IncludeStartExcludeEnd): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')), $bounds);
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
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     *
     * @param string $format the format of the outputted date string
     */
    public function toIso80000(string $format): string
    {
        return $this->bounds->buildIso80000($this->startDate->format($format), $this->endDate->format($format));
    }

    /**
     * Returns the mathematical representation of an instance as a left close, right open interval.
     *
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     *
     * @param string $format the format of the outputted date string
     */
    public function toBourbaki(string $format): string
    {
        return $this->bounds->buildBourbaki($this->startDate->format($format), $this->endDate->format($format));
    }

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     */
    public function toIso8601(string $format = 'Y-m-d\TH:i:s.u\Z'): string
    {
        $utc = new DateTimeZone('UTC');

        return $this->startDate->setTimezone($utc)->format($format)
            .'/'.$this->endDate->setTimezone($utc)->format($format);
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
     * @return array{startDate:string, endDate:string, startDateIncluded:bool, endDateIncluded:bool}
     */
    public function jsonSerialize(): array
    {
        $format = 'Y-m-d\TH:i:s.u\Z';
        $utc = new DateTimeZone('UTC');

        return [
            'startDate' => $this->startDate->setTimezone($utc)->format($format),
            'endDate' => $this->endDate->setTimezone($utc)->format($format),
            'startDateIncluded' => $this->bounds->isStartIncluded(),
            'endDateIncluded' => $this->bounds->isEndIncluded(),
        ];
    }

    /**************************************************
     * Duration properties
     **************************************************/

    public function timeDuration(): int
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    public function dateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
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
     */
    public function durationCompare(Period|Duration|DateInterval|string $duration): int
    {
        return $this->startDate->add($this->dateInterval()) <=> $this->startDate->add(self::filterDuration($duration));
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     *
     */
    public function durationGreaterThan(Period|Duration|DateInterval|string $duration): bool
    {
        return 1 === $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is greater than or equal to the submitted one.
     *
     */
    public function durationGreaterThanOrEquals(Period|Duration|DateInterval|string $duration): bool
    {
        return 0 <= $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is equal to the submitted one.
     *
     */
    public function durationEquals(Period|Duration|DateInterval|string $duration): bool
    {
        return 0 === $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is greater than or equal to the submitted one.
     *
     */
    public function durationLessThanOrEquals(Period|Duration|DateInterval|string $duration): bool
    {
        return 0 >= $this->durationCompare($duration);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     *
     */
    public function durationLessThan(Period|Duration|DateInterval|string $duration): bool
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
     */
    public function isBefore(Period|DatePoint|DateTimeInterface|string $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->endDate <= $timeSlot->startDate;
        }

        $datePoint = self::filterDatePoint($timeSlot);

        return $this->endDate < $datePoint
            || ($this->endDate == $datePoint && !$this->bounds->isEndIncluded());
    }

    /**
     * Tells whether the current instance end date abuts the interval start date.
     *
     * [--------------------)
     *                      [--------------------)
     */
    public function bordersOnStart(self $timeSlot): bool
    {
        return $this->endDate == $timeSlot->startDate
            && !($this->bounds->isEndIncluded() && $timeSlot->bounds->isStartIncluded());
    }

    /**
     * Tells whether the current instance end date meets the interval start date.
     *
     * (--------------------]
     *                      [--------------------)
     */
    public function meetsOnStart(self $timeSlot): bool
    {
        return $this->endDate == $timeSlot->startDate
            && $this->bounds->isEndIncluded()
            && $timeSlot->bounds->isStartIncluded();
    }

    /**
     * Tells whether two intervals share the same start date endpoint
     * and the same starting boundary type.
     *
     *    [----------)
     *    [--------------------)
     *
     * or
     *
     *    [--------------------)
     *    [---------)
     */
    public function isStartedBy(Period|DatePoint|DateTimeInterface|string $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->startDate == $timeSlot->startDate
                && $this->bounds->equalsStart($timeSlot->bounds);
        }

        return self::filterDatePoint($timeSlot) == $this->startDate && $this->bounds->isStartIncluded();
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
     */
    public function contains(Period|DatePoint|DateTimeInterface|string $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->containsInterval($timeSlot);
        }

        return $this->containsDatePoint(self::filterDatePoint($timeSlot), $this->bounds);
    }

    /**
     * Tells whether an instance fully contains another instance.
     *
     * [--------------------)
     *     [----------)
     */
    private function containsInterval(self $timeSlot): bool
    {
        return match (true) {
            $this->startDate < $timeSlot->startDate && $this->endDate > $timeSlot->endDate
                => true,
            $this->startDate == $timeSlot->startDate && $this->endDate == $timeSlot->endDate
                => $this->bounds === $timeSlot->bounds || $this->bounds === Bounds::IncludeAll,
            $this->startDate == $timeSlot->startDate
                => ($this->bounds->equalsStart($timeSlot->bounds) || $this->bounds->isStartIncluded())
                    && $this->containsDatePoint($this->startDate->add($timeSlot->dateInterval()), $this->bounds),
            $this->endDate == $timeSlot->endDate
                => ($this->bounds->equalsEnd($timeSlot->bounds) || $this->bounds->isEndIncluded())
                    && $this->containsDatePoint($this->endDate->sub($timeSlot->dateInterval()), $this->bounds),
            default
                => false,
        };
    }

    /**
     * Tells whether an instance contains a date endpoint.
     *
     * [------|------------)
     */
    private function containsDatePoint(DateTimeInterface $datePoint, Bounds $bounds): bool
    {
        return match ($bounds) {
            Bounds::ExcludeAll => $datePoint > $this->startDate && $datePoint < $this->endDate,
            Bounds::IncludeAll => $datePoint >= $this->startDate && $datePoint <= $this->endDate,
            Bounds::ExcludeStartIncludeEnd => $datePoint > $this->startDate && $datePoint <= $this->endDate,
            Bounds::IncludeStartExcludeEnd => $datePoint >= $this->startDate && $datePoint < $this->endDate,
        };
    }

    /**
     * Tells whether two intervals share the same date endpoints.
     *
     * [--------------------)
     * [--------------------)
     */
    public function equals(self $timeSlot): bool
    {
        return $this->startDate == $timeSlot->startDate
            && $this->endDate == $timeSlot->endDate
            && $this->bounds === $timeSlot->bounds;
    }

    /**
     * Tells whether two intervals share the same end date endpoint
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
     */
    public function isEndedBy(Period|DatePoint|DateTimeInterface|string $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $this->endDate == $timeSlot->endDate
                && $this->bounds->equalsEnd($timeSlot->bounds);
        }

        return self::filterDatePoint($timeSlot) == $this->endDate && $this->bounds->isEndIncluded();
    }

    /**
     * Tells whether the current instance start date meets the interval end date.
     *
     *                      [--------------------)
     * (--------------------]
     */
    public function meetsOnEnd(self $timeSlot): bool
    {
        return $this->startDate == $timeSlot->endDate
            && $this->bounds->isStartIncluded()
            && $timeSlot->bounds->isEndIncluded();
    }

    /**
     * Tells whether the current instance start date abuts the interval end date.
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
     */
    public function isAfter(Period|DatePoint|DateTimeInterface|string $timeSlot): bool
    {
        if ($timeSlot instanceof self) {
            return $timeSlot->isBefore($this);
        }

        $datePoint = self::filterDatePoint($timeSlot);

        return $this->startDate > $datePoint
            || ($this->startDate == $datePoint && ! $this->bounds->isStartIncluded());
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
     * Tells whether two intervals meets.
     *
     * (--------------------]
     *                      [--------------------)
     * or
     *                      [--------------------)
     * (--------------------]
     */
    public function meets(self $timeSlot): bool
    {
        return $this->meetsOnEnd($timeSlot) || $this->meetsOnStart($timeSlot);
    }

    /**
     * Tells whether two intervals overlaps.
     *
     * [--------------------)
     *          [--------------------)
     */
    public function overlaps(self $timeSlot): bool
    {
        return match (true) {
            $this->meets($timeSlot) => true,
            default => !$this->abuts($timeSlot) && $this->startDate < $timeSlot->endDate && $this->endDate > $timeSlot->startDate,
        };
    }

    /**************************************************
     * Manipulating instance duration
     **************************************************/

    /**
     * Returns the difference between two instances expressed in seconds.
     */
    public function timeDurationDiff(self $period): int
    {
        return $this->timeDuration() - $period->timeDuration();
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
     * The returned DatePeriod object contains only DateTimeImmutable objects.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     *
     * @return DatePeriod|DateTimeImmutable[]
     */
    public function dateRangeForward(Period|Duration|DateInterval|string $timeDelta, InitialDatePresence $startDatePresence = InitialDatePresence::Included): DatePeriod
    {
        return new DatePeriod(
            $this->startDate,
            self::filterDuration($timeDelta),
            $this->endDate,
            $startDatePresence === InitialDatePresence::Excluded ? DatePeriod::EXCLUDE_START_DATE : 0
        );
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from the instance ending.
     *
     * @return Generator|DateTimeImmutable[]
     */
    public function dateRangeBackwards(Period|Duration|DateInterval|string $timeDelta, InitialDatePresence $endDatePresence = InitialDatePresence::Included): Generator
    {
        $timeDelta = self::filterDuration($timeDelta);
        $date = $this->endDate;
        if ($endDatePresence === InitialDatePresence::Excluded) {
            $date = $this->endDate->sub($timeDelta);
        }

        while ($date > $this->startDate) {
            yield $date;
            $date = $date->sub($timeDelta);
        }
    }

    /**
     * Allows splitting an instance in smaller Period objects according to a given interval.
     *
     * The returned iterable Interval set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the starting date endpoint of the parent object.</li>
     * <li>The last returned object MUST share the ending date endpoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST start immediately after the previously returned object</li>
     * </ul>
     *
     * @return Generator|Period[]
     */
    public function splitForward(Period|Duration|DateInterval|string $duration): Generator
    {
        $duration = self::filterDuration($duration);
        /** @var DateTimeImmutable $startDate */
        foreach ($this->dateRangeForward($duration) as $startDate) {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }

            yield self::fromDate($startDate, $endDate, $this->bounds);
        }
    }

    /**
     * Allows splitting an instance in smaller Period objects according to a given interval.
     *
     * The returned iterable Period set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the ending date endpoint of the parent object.</li>
     * <li>The last returned object MUST share the starting date endpoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST end immediately before the previously returned object</li>
     * </ul>
     *
     * @return Generator|Period[]
     */
    public function splitBackwards(Period|Duration|DateInterval|string $duration): Generator
    {
        $endDate = $this->endDate;
        $duration = self::filterDuration($duration);
        do {
            $startDate = $endDate->sub($duration);
            if ($startDate < $this->startDate) {
                $startDate = $this->startDate;
            }
            yield new self($startDate, $endDate, $this->bounds);

            $endDate = $startDate;
        } while ($endDate > $this->startDate);
    }

    /**************************************************
     * Manipulation instance endpoints and bounds
     **************************************************/

    /**
     * Returns the computed difference between two overlapping instances as
     * an array containing Period objects or the null value.
     *
     * The array will always contain 2 elements:
     *
     * <ul>
     * <li>an NULL filled array if both objects have the same date endpoints</li>
     * <li>one Period object and NULL if both objects share one date endpoint</li>
     * <li>two Period objects if both objects share no date endpoint</li>
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
            return new Sequence($merge->startingOn($intersect->endDate)->boundedBy(
                $intersect->bounds->isEndIncluded() ? $merge->bounds->excludeStart() : $merge->bounds->includeStart()
            ));
        }

        if ($merge->endDate == $intersect->endDate) {
            return new Sequence($merge->endingOn($intersect->startDate)->boundedBy(
                $intersect->bounds->isStartIncluded() ? $merge->bounds->excludeEnd() : $merge->bounds->includeEnd()
            ));
        }

        return new Sequence(
            $merge->endingOn($intersect->startDate)->boundedBy(
                $intersect->bounds->isStartIncluded() ? $merge->bounds->excludeEnd() : $merge->bounds->includeEnd()
            ),
            $merge->startingOn($intersect->endDate)->boundedBy(
                $intersect->bounds->isEndIncluded() ? $merge->bounds->excludeStart() : $merge->bounds->includeStart()
            ),
        );
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
     * @throws UnprocessableInterval If both instance overlaps
     */
    public function gap(self $period): self
    {
        if ($this->overlaps($period)) {
            throw UnprocessableInterval::dueToMissingGaps();
        }

        if ($period->startDate > $this->startDate) {
            $bounds = $this->bounds->isEndIncluded() ? $this->bounds->excludeStart() : $this->bounds->includeStart();
            $bounds = $period->bounds->isStartIncluded() ? $bounds->excludeEnd() : $bounds->includeEnd();

            return new self($this->endDate, $period->startDate, $bounds);
        }

        return new self($period->endDate, $this->startDate, $this->bounds);
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
    public function subtract(self ...$periods): Sequence
    {
        return (new Sequence($this))->subtract(new Sequence(...$periods));
    }

    /**
     * Returns the computed intersection between two or more instances as a new instance.
     *
     * [--------------------)
     *          ∩
     *                 [----------)
     *          =
     *                 [----)
     *
     * @throws UnprocessableInterval If both objects do not overlap
     */
    public function intersect(self ...$periods): self
    {
        return array_reduce($periods, function (Period $carry, Period $period): Period {
            if (!$carry->overlaps($period)) {
                throw UnprocessableInterval::dueToMissingOverlaps();
            }

            $bounds = $carry->bounds;

            $startDate = $carry->startDate;
            if ($period->startDate > $startDate) {
                $bounds = $bounds->replaceStart($period->bounds);
                $startDate = $period->startDate;
            }

            $endDate = $carry->endDate;
            if ($period->endDate < $endDate) {
                $bounds = $bounds->replaceEnd($period->bounds);
                $endDate = $period->endDate;
            }

            $intersect = new self($startDate, $endDate, $bounds);
            if ($intersect->equals($carry)) {
                return $carry;
            }

            return $intersect;
        }, $this);
    }

    /**
     * Returns the computed union between two instances as a Sequence.
     */
    public function union(self ...$periods): Sequence
    {
        return (new Sequence($this, ...$periods))->unions();
    }

    /**
     * Merges one or more instances to return a new instance.
     * The resulting instance represents the largest duration possible.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     *
     * [--------------------)
     *          +
     *                 [----------)
     *          =
     * [--------------------------)
     */
    public function merge(self ...$periods): self
    {
        return array_reduce($periods, function (Period $carry, Period $period): Period {
            if ($carry->startDate > $period->startDate) {
                $carry = new self(
                    $period->startDate,
                    $carry->endDate,
                    $carry->bounds->replaceStart($period->bounds)
                );
            }

            if ($carry->endDate < $period->endDate) {
                $carry = new self(
                    $carry->startDate,
                    $period->endDate,
                    $carry->bounds->replaceEnd($period->bounds)
                );
            }

            return $carry;
        }, $this);
    }

    /**************************************************
     * Mutation methods
     **************************************************/

    /**
     * Returns an instance with the specified starting date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     */
    public function startingOn(DatePoint|DateTimeInterface|string $startDate): self
    {
        $startDate = self::filterDatePoint($startDate);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate, $this->bounds);
    }

    /**
     * Returns an instance with the specified ending date endpoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     */
    public function endingOn(DatePoint|DateTimeInterface|string $endDate): self
    {
        $endDate = self::filterDatePoint($endDate);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate, $this->bounds);
    }

    /**
     * Returns an instance with the specified boundary type.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the specified range type.
     */
    public function boundedBy(Bounds $bounds): self
    {
        if ($bounds === $this->bounds) {
            return $this;
        }

        return new self($this->startDate, $this->endDate, $bounds);
    }

    /**
     * Returns a new instance with the specified duration after the start date.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     */
    public function withDurationAfterStart(Period|Duration|DateInterval|string $duration): self
    {
        return $this->endingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with the specified duration before the end date.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     */
    public function withDurationBeforeEnd(Period|Duration|DateInterval|string $duration): self
    {
        return $this->startingOn($this->endDate->sub(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new starting date endpoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date endpoint.
     */
    public function moveStartDate(Period|Duration|DateInterval|string $duration): self
    {
        return $this->startingOn($this->startDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance with a new ending date endpoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date endpoint.
     */
    public function moveEndDate(Period|Duration|DateInterval|string $duration): self
    {
        return $this->endingOn($this->endDate->add(self::filterDuration($duration)));
    }

    /**
     * Returns a new instance where the date endpoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function move(Period|Duration|DateInterval|string $duration): self
    {
        $duration = self::filterDuration($duration);

        return $this->newInstance(
            new self($this->startDate->add($duration), $this->endDate->add($duration), $this->bounds)
        );
    }

    private function newInstance(Period $interval): Period
    {
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * subtracted from the starting date endpoint and added to the ending date endpoint.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrunken.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function expand(Period|Duration|DateInterval|string $duration): self
    {
        $duration = self::filterDuration($duration);

        return $this->newInstance(
            new self($this->startDate->sub($duration), $this->endDate->add($duration), $this->bounds)
        );
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective second interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToSecond(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->second()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->second()->endDate;
        if ($this->endDate->add(new DateInterval('PT1S')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective minute interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToMinute(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->minute()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->minute()->endDate;
        if ($this->endDate->add(new DateInterval('PT1M')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective hour interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToHour(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->hour()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->hour()->endDate;
        if ($this->endDate->add(new DateInterval('PT1H')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective day interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToDay(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->day()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->day()->endDate;
        if ($this->endDate->add(new DateInterval('P1D')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective iso week interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToIsoWeek(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->isoWeek()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->isoWeek()->endDate;
        if ($this->endDate->add(new DateInterval('P7D')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective month interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToMonth(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->month()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->month()->endDate;
        if ($this->endDate->add(new DateInterval('P1M')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective quarter interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToQuarter(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->quarter()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->quarter()->endDate;
        if ($this->endDate->add(new DateInterval('P3M')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective semester interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToSemester(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->semester()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->semester()->endDate;
        if ($this->endDate->add(new DateInterval('P6M')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective year interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToYear(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->year()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->year()->endDate;
        if ($this->endDate->add(new DateInterval('P1Y')) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }

    /**
     * Returns a new instance which snaps the startDate and endDate to their respective iso year interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new date endpoints.
     */
    public function snapToIsoYear(): self
    {
        $startDate = DatePoint::fromDate($this->startDate)->isoYear()->startDate;
        if ($startDate == $this->startDate) {
            $startDate = $this->startDate;
        }

        $endDate = DatePoint::fromDate($this->endDate)->isoYear()->endDate;
        if ($this->endDate->setISODate((int) $this->endDate->format('Y') + 1, 1) == $endDate) {
            $endDate = $this->endDate;
        }

        return $this->newInstance(new self($startDate, $endDate, $this->bounds));
    }
}
