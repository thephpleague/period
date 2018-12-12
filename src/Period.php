<?php

/**
 * League.Period (https://period.thephpleague.com).
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
use function intdiv;

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

    public const CALENDAR_YEAR = 'YEAR';
    public const CALENDAR_ISOYEAR = 'ISOYEAR';
    public const CALENDAR_SEMESTER = 'SEMESTER';
    public const CALENDAR_QUARTER = 'QUARTER';
    public const CALENDAR_MONTH = 'MONTH';
    public const CALENDAR_ISOWEEK = 'ISOWEEK';
    public const CALENDAR_DAY = 'DAY';
    public const CALENDAR_HOUR = 'HOUR';
    public const CALENDAR_MINUTE = 'MINUTE';
    public const CALENDAR_SECOND = 'SECOND';

    /**
     * The starting included datepoint.
     *
     * @var DateTimeImmutable
     */
    private $startDate;

    /**
     * The ending excluded datepoint.
     *
     * @var DateTimeImmutable
     */
    private $endDate;

    /**
     * @inheritdoc
     */
    public static function __set_state(array $interval)
    {
        return new self($interval['startDate'], $interval['endDate']);
    }

    /**
     * Creates new instance from a starting datepoint and a duration.
     */
    public static function after($datepoint, $duration): self
    {
        $datepoint = Datepoint::create($datepoint);

        return new self($datepoint, $datepoint->add(Duration::create($duration)));
    }

    /**
     * Creates new instance from a ending datepoint and a duration.
     */
    public static function before($datepoint, $duration): self
    {
        $datepoint = Datepoint::create($datepoint);

        return new self($datepoint->sub(Duration::create($duration)), $datepoint);
    }

    /**
     * Creates new instance where the given duration is simultaneously
     * substracted from and added to the datepoint.
     */
    public static function around($datepoint, $duration): self
    {
        $datepoint = Datepoint::create($datepoint);
        $duration = Duration::create($duration);

        return new self($datepoint->sub($duration), $datepoint->add($duration));
    }

    /**
     * Creates new instance for a specific year.
     */
    public static function fromYear(int $year): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * Creates new instance for a specific ISO year.
     */
    public static function fromIsoYear(int $year): self
    {
        return new self(
            (new DateTimeImmutable())->setISODate($year, 1)->setTime(0, 0),
            (new DateTimeImmutable())->setISODate(++$year, 1)->setTime(0, 0)
        );
    }

    /**
     * Creates new instance for a specific year and semester.
     */
    public static function fromSemester(int $year, int $semester = 1): self
    {
        $month = (($semester - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P6M')));
    }

    /**
     * Creates new instance for a specific year and quarter.
     */
    public static function fromQuarter(int $year, int $quarter = 1): self
    {
        $month = (($quarter - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')));
    }

    /**
     * Creates new instance for a specific year and month.
     */
    public static function fromMonth(int $year, int $month = 1): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')));
    }

    /**
     * Creates new instance for a specific ISO8601 week.
     */
    public static function fromIsoWeek(int $year, int $week = 1): self
    {
        $startDate = (new DateTimeImmutable())->setISODate($year, $week, 1)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P7D')));
    }

    /**
     * Creates new instance for a specific year, month and day.
     */
    public static function fromDay(int $year, int $month = 1, int $day = 1): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime(0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')));
    }

    /**
     * Creates new instance for a specific year, month, day and hour.
     */
    public static function fromHour(int $year, int $month = 1, int $day = 1, int $hour = 0): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime($hour, 0);

        return new self($startDate, $startDate->add(new DateInterval('PT1H')));
    }

    /**
     * Creates new instance for a specific year, month, day, hour and minute.
     */
    public static function fromMinute(int $year, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0): self
    {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime($hour, $minute);

        return new self($startDate, $startDate->add(new DateInterval('PT1M')));
    }

    /**
     * Creates new instance for a specific year, month, day, hour, minute and second.
     */
    public static function fromSecond(
        int $year,
        int $month = 1,
        int $day = 1,
        int $hour = 0,
        int $minute = 0,
        int $second = 0
    ): self {
        $startDate = (new DateTimeImmutable())->setDate($year, $month, $day)->setTime($hour, $minute, $second);

        return new self($startDate, $startDate->add(new DateInterval('PT1S')));
    }

    /**
     * Creates new instance corresponding to a specific datepoint.
     */
    public static function fromDatepoint($datepoint): self
    {
        $datepoint = Datepoint::create($datepoint);

        return new self($datepoint, $datepoint);
    }

    /**
     * Creates a new instance from a datepoint and a calendar reference.
     *
     * The datepoint is contained or start the interval and the duration is
     * equals to the calendar reference duration.
     */
    public static function fromCalendar($datepoint, string $calendar): self
    {
        $datepoint = Datepoint::create($datepoint);
        switch ($calendar) {
            case self::CALENDAR_HOUR:
                $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0);

                return new self($startDate, $startDate->add(new DateInterval('PT1H')));

            case self::CALENDAR_MINUTE:
                $startDate = $datepoint->setTime((int) $datepoint->format('H'), (int) $datepoint->format('i'));

                return new self($startDate, $startDate->add(new DateInterval('PT1M')));

            case self::CALENDAR_SECOND:
                $startDate = $datepoint->setTime(
                    (int) $datepoint->format('H'),
                    (int) $datepoint->format('i'),
                    (int) $datepoint->format('s')
                );

                return new self($startDate, $startDate->add(new DateInterval('PT1S')));

            case self::CALENDAR_DAY:
                $startDate = $datepoint->setTime(0, 0);

                return new self($startDate, $startDate->add(new DateInterval('P1D')));

            case self::CALENDAR_ISOWEEK:
                $startDate = $datepoint
                    ->setTime(0, 0)
                    ->setISODate((int) $datepoint->format('o'), (int) $datepoint->format('W'), 1);

                return new self($startDate, $startDate->add(new DateInterval('P7D')));

            case self::CALENDAR_MONTH:
                $startDate = $datepoint
                    ->setTime(0, 0)
                    ->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1);

                return new self($startDate, $startDate->add(new DateInterval('P1M')));

            case self::CALENDAR_QUARTER:
                $startDate = $datepoint
                    ->setTime(0, 0)
                    ->setDate((int) $datepoint->format('Y'), (intdiv((int) $datepoint->format('n'), 3) * 3) + 1, 1);

                return new self($startDate, $startDate->add(new DateInterval('P3M')));

            case self::CALENDAR_SEMESTER:
                $startDate = $datepoint
                    ->setTime(0, 0)
                    ->setDate((int) $datepoint->format('Y'), (intdiv((int) $datepoint->format('n'), 6) * 6) + 1, 1);

                return new self($startDate, $startDate->add(new DateInterval('P6M')));

            case self::CALENDAR_YEAR:
                $startDate = $datepoint->setTime(0, 0)->setDate((int) $datepoint->format('Y'), 1, 1);

                return new self($startDate, $startDate->add(new DateInterval('P1Y')));

            case self::CALENDAR_ISOYEAR:
                $datepoint = $datepoint->setTime(0, 0);
                $year = (int) $datepoint->format('o');

                return new self($datepoint->setISODate($year, 1), $datepoint->setISODate(++$year, 1));

            default:
                throw new Exception('Unknown Calendar interval');
        }
    }

    /**
     * Creates new instance from a DatePeriod.
     */
    public static function fromDatePeriod(DatePeriod $datePeriod): self
    {
        return new self($datePeriod->getStartDate(), $datePeriod->getEndDate());
    }

    /**
     * Creates a new instance.
     *
     * @param mixed $startDate the starting included datepoint
     * @param mixed $endDate   the ending excluded datepoint
     *
     * @throws Exception If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = Datepoint::create($startDate);
        $endDate = Datepoint::create($endDate);
        if ($startDate > $endDate) {
            throw new Exception('The ending datepoint must be greater or equal to the starting datepoint');
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Returns the starting included datepoint.
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Returns the ending excluded datepoint.
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Returns the instance duration as expressed in seconds.
     */
    public function getTimestampInterval(): float
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * Returns the instance duration as a DateInterval object.
     */
    public function getDateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     */
    public function getDatePeriod($duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, Duration::create($duration), $this->endDate, $option);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance backwards starting from
     * the instance ending datepoint.
     */
    public function getDatePeriodBackwards($duration, int $option = 0): iterable
    {
        $duration = Duration::create($duration);
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
     * Returns the string representation as a ISO8601 interval format.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString()
    {
        $interval = $this->jsonSerialize();

        return $interval['startDate'].'/'.$interval['endDate'];
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
        $utc = new DateTimeZone('UTC');

        return [
            'startDate' => $this->startDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
            'endDate' => $this->endDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
        ];
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
    public function format(string $format): string
    {
        return '['.$this->startDate->format($format).', '.$this->endDate->format($format).')';
    }

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
        return $this->endDate <=> $this->startDate->add($interval->getDateInterval());
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

    /**
     * Tells whether two intervals share the same datepoints.
     *
     * [--------------------)
     * [--------------------)
     */
    public function equals(self $interval): bool
    {
        return $this->startDate == $interval->startDate
            && $this->endDate == $interval->endDate;
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
        return $this->startDate == $interval->endDate
            || $this->endDate == $interval->startDate;
    }

    /**
     * Tells whether two intervals overlaps.
     *
     * [--------------------)
     *          [--------------------)
     */
    public function overlaps(self $interval): bool
    {
        return $this->startDate < $interval->endDate
            && $this->endDate > $interval->startDate;
    }

    /**
     * Tells whether an interval is entirely after the specified index.
     * The index can be a DateTimeInterface object or another Period object.
     *
     *                          [--------------------)
     * [--------------------)
     */
    public function isAfter($index): bool
    {
        if ($index instanceof self) {
            return $this->startDate >= $index->endDate;
        }

        return $this->startDate > Datepoint::create($index);
    }

    /**
     * Tells whether an instance is entirely before the specified index.
     *
     * The index can be a DateTimeInterface object or another Period object.
     *
     * [--------------------)
     *                          [--------------------)
     */
    public function isBefore($index): bool
    {
        if ($index instanceof self) {
            return $this->endDate <= $index->startDate;
        }

        return $this->endDate <= Datepoint::create($index);
    }

    /**
     * Tells whether an instance fully contains the specified index.
     *
     * The index can be a DateTimeInterface object or another Period object.
     *
     */
    public function contains($index): bool
    {
        if ($index instanceof self) {
            return $this->containsInterval($index);
        }

        return $this->containsDatepoint(Datepoint::create($index));
    }

    /**
     * Tells whether an instance fully contains another instance.
     *
     * [--------------------)
     *     [----------)
     */
    private function containsInterval(self $interval): bool
    {
        return $this->containsDatepoint($interval->startDate)
            && ($interval->endDate >= $this->startDate && $interval->endDate <= $this->endDate);
    }

    /**
     * Tells whether an instance contains a datepoint.
     *
     * [------|------------)
     */
    private function containsDatepoint(DateTimeInterface $datepoint): bool
    {
        return $datepoint >= $this->startDate && $datepoint < $this->endDate;
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
     * @return iterable<Period>
     */
    public function split($duration): iterable
    {
        $duration = Duration::create($duration);
        foreach ($this->getDatePeriod($duration) as $startDate) {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }

            yield new self($startDate, $endDate);
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
     * @return iterable<Period>
     */
    public function splitBackwards($duration): iterable
    {
        $endDate = $this->endDate;
        $duration = Duration::create($duration);
        do {
            $startDate = $endDate->sub($duration);
            if ($startDate < $this->startDate) {
                $startDate = $this->startDate;
            }
            yield new self($startDate, $endDate);

            $endDate = $startDate;
        } while ($endDate > $this->startDate);
    }

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

        return new self(
            ($interval->startDate > $this->startDate) ? $interval->startDate : $this->startDate,
            ($interval->endDate < $this->endDate) ? $interval->endDate : $this->endDate
        );
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
            return [$merge->startingOn($intersect->endDate), null];
        }

        if ($merge->endDate == $intersect->endDate) {
            return [$merge->endingOn($intersect->startDate), null];
        }

        return [
            $merge->endingOn($intersect->startDate),
            $merge->startingOn($intersect->endDate),
        ];
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

        if ($interval->startDate > $this->startDate) {
            return new self($this->endDate, $interval->startDate);
        }

        return new self($interval->endDate, $this->startDate);
    }

    /**
     * Returns the difference between two instances expressed in seconds.
     */
    public function timestampIntervalDiff(self $interval): float
    {
        return $this->getTimestampInterval() - $interval->getTimestampInterval();
    }

    /**
     * Returns the difference between two instances expressed with a DateInterval object.
     */
    public function dateIntervalDiff(self $interval): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($interval->getDateInterval()));
    }

    /**
     * Returns an instance with the specified starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     */
    public function startingOn($datepoint): self
    {
        $startDate = Datepoint::create($datepoint);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate);
    }

    /**
     * Returns an instance with the specified ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     */
    public function endingOn($datepoint): self
    {
        $endDate = Datepoint::create($datepoint);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate);
    }

    /**
     * Returns a new instance with a new ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     */
    public function withDurationAfterStart($duration): self
    {
        return $this->endingOn($this->startDate->add(Duration::create($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     */
    public function withDurationBeforeEnd($duration): self
    {
        return $this->startingOn($this->endDate->sub(Duration::create($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     */
    public function moveStartDate($duration): self
    {
        return $this->startingOn($this->startDate->add(Duration::create($duration)));
    }

    /**
     * Returns a new instance with a new ending datepoint
     * moved forward or backward by the given interval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     */
    public function moveEndDate($duration): self
    {
        return $this->endingOn($this->endDate->add(Duration::create($duration)));
    }

    /**
     * Returns a new instance where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     */
    public function move($duration): self
    {
        $duration = Duration::create($duration);
        $interval = new self($this->startDate->add($duration), $this->endDate->add($duration));
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * substracted from the starting datepoint and added to the ending datepoint.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrinked.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     */
    public function expand($duration): self
    {
        $duration = Duration::create($duration);
        $interval = new self($this->startDate->sub($duration), $this->endDate->add($duration));
        if ($this->equals($interval)) {
            return $this;
        }

        return $interval;
    }

    /**
     * Merges one or more instances to return a new instance.
     * The resulting instance represents the largest duration possible.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * [--------------------)
     *          U
     *                 [----------)
     *          =
     * [--------------------------)
     *
     *
     * @param Period ...$intervals
     */
    public function merge(self $interval, self ...$intervals): self
    {
        $intervals[] = $interval;
        $carry = $this;
        foreach ($intervals as $interval) {
            if ($carry->startDate > $interval->startDate) {
                $carry = $carry->startingOn($interval->startDate);
            }

            if ($carry->endDate < $interval->endDate) {
                $carry = $carry->endingOn($interval->endDate);
            }
        }

        return $carry;
    }
}
