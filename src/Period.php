<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
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
use const FILTER_VALIDATE_INT;
use function array_unshift;
use function filter_var;
use function gettype;
use function intdiv;
use function sprintf;

/**
 * A immutable value object class to manipulate Time interval.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
final class Period implements Interval, JsonSerializable
{
    private const ISO8601_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /**
     * Period starting included datepoint.
     *
     * @var DateTimeImmutable
     */
    private $startDate;

    /**
     * Period ending excluded datepoint.
     *
     * @var DateTimeImmutable
     */
    private $endDate;

    /**
     * @inheritdoc
     */
    public static function __set_state(array $period)
    {
        return new self($period['startDate'], $period['endDate']);
    }

    /**
     * Creates a new instance.
     *
     * @param mixed $startDate the interval start datepoint
     * @param mixed $endDate   the interval end datepoint
     *
     * @throws Exception If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = datepoint($startDate);
        $endDate = datepoint($endDate);
        if ($startDate > $endDate) {
            throw new Exception('The ending datepoint must be greater or equal to the starting datepoint');
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Creates new instance from a DatePeriod.
     *
     * @throws Exception If the submitted DatePeriod lacks an end datepoint.
     *                   This is possible if the DatePeriod was created using
     *                   recurrences instead of a end datepoint.
     *                   https://secure.php.net/manual/en/dateperiod.getenddate.php
     */
    public static function createFromDatePeriod(DatePeriod $datePeriod): self
    {
        $endDate = $datePeriod->getEndDate();
        if ($endDate instanceof DateTimeInterface) {
            return new self($datePeriod->getStartDate(), $endDate);
        }

        throw new Exception('The submitted DatePeriod object does not contain an end datepoint');
    }

    /**
     * Creates new instance from a starting point and an interval.
     */
    public static function createFromDurationAfterStart($datepoint, $duration): self
    {
        $startDate = datepoint($datepoint);

        return new self($startDate, $startDate->add(duration($duration)));
    }

    /**
     * Creates new instance from a ending excluded datepoint and an interval.
     */
    public static function createFromDurationBeforeEnd($datepoint, $duration): self
    {
        $endDate = datepoint($datepoint);

        return new self($endDate->sub(duration($duration)), $endDate);
    }

    /**
     * Creates new instance for a specific year.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     */
    public static function createFromYear($int_or_datepoint): self
    {
        if (is_int($int_or_datepoint)) {
            $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)->setDate($int_or_datepoint, 1, 1);

            return new self($startDate, $startDate->add(new DateInterval('P1Y')));
        }

        $datepoint = datepoint($int_or_datepoint);
        $startDate = $datepoint->setTime(0, 0, 0, 0)->setDate((int) $datepoint->format('Y'), 1, 1);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * Creates new instance for a specific ISO year.
     *
     * @param mixed $iso_year a year as an int or a datepoint
     */
    public static function createFromISOYear($iso_year): self
    {
        if (is_int($iso_year)) {
            $datepoint = (new DateTimeImmutable())->setTime(0, 0, 0, 0);

            return new self(
                $datepoint->setISODate($iso_year, 1, 1),
                $datepoint->setISODate(++$iso_year, 1, 1)
            );
        }

        $datepoint = datepoint($iso_year)->setTime(0, 0, 0, 0);
        $iso_year = (int) $datepoint->format('o');

        return new self(
            $datepoint->setISODate($iso_year, 1, 1),
            $datepoint->setISODate(++$iso_year, 1, 1)
        );
    }

    /**
     * Creates new instance for a specific semester in a given year.
     *
     * @param mixed    $int_or_datepoint a year as an int or a datepoint
     * @param null|int $semester         a semester index from 1 to 2 included
     */
    public static function createFromSemester($int_or_datepoint, int $semester = null): self
    {
        if (is_int($int_or_datepoint)) {
            $month = ((self::filterRange(self::filterInt($semester, 'semester'), 1, 2) - 1) * 6) + 1;
            $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
                ->setDate(self::filterInt($int_or_datepoint, 'year'), $month, 1);

            return new self($startDate, $startDate->add(new DateInterval('P6M')));
        }

        $datepoint = datepoint($int_or_datepoint);
        $month = (intdiv((int) $datepoint->format('n'), 6) * 6) + 1;
        $startDate = $datepoint->setTime(0, 0, 0, 0)
            ->setDate((int) $datepoint->format('Y'), $month, 1);

        return new self($startDate, $startDate->add(new DateInterval('P6M')));
    }

    /**
     * Filters the input integer.
     *
     * @param int|string|null $value
     *
     * @throws Exception if the given value can not be converted to an int.
     */
    private static function filterInt($value, string $name): int
    {
        if (null !== $value && false !== ($int = filter_var($value, FILTER_VALIDATE_INT))) {
            return $int;
        }

        throw new Exception(sprintf('The %s value must be an integer %s given', $name, gettype($value)));
    }

    /**
     * Filters a integer according to a range.
     *
     * @param int $value the value to validate
     * @param int $min   the minimum value
     * @param int $max   the maximal value
     *
     * @throws Exception If the value is not in the range
     */
    private static function filterRange(int $value, int $min, int $max): int
    {
        if ($value >= $min && $value <= $max) {
            return $value;
        }

        throw new Exception('The submitted value is not contained within a valid range');
    }

    /**
     * Creates new instance for a specific quarter in a given year.
     *
     * @param mixed    $int_or_datepoint a year as an int or a datepoint
     * @param null|int $quarter          quarter index from 1 to 4 included
     */
    public static function createFromQuarter($int_or_datepoint, int $quarter = null): self
    {
        if (is_int($int_or_datepoint)) {
            $month = ((self::filterRange(self::filterInt($quarter, 'quarter'), 1, 4) - 1) * 3) + 1;
            $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
                ->setDate(self::filterInt($int_or_datepoint, 'year'), $month, 1);

            return new self($startDate, $startDate->add(new DateInterval('P3M')));
        }

        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $month = (intdiv((int) $datepoint->format('n'), 3) * 3) + 1;
        $startDate = $datepoint
            ->setDate((int) $datepoint->format('Y'), $month, 1);

        return new self($startDate, $startDate->add(new DateInterval('P3M')));
    }

    /**
     * Creates new instance for a specific year and month.
     *
     * @param mixed    $int_or_datepoint a year as an int or a datepoint
     * @param int|null $month            month index from 1 to 12 included
     */
    public static function createFromMonth($int_or_datepoint, int $month = null): self
    {
        if (is_int($int_or_datepoint)) {
            $month = self::filterRange(self::filterInt($month, 'month'), 1, 12);
            $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
                ->setDate(self::filterInt($int_or_datepoint, 'year'), $month, 1);

            return new self($startDate, $startDate->add(new DateInterval('P1M')));
        }

        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $startDate = $datepoint
            ->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1);

        return new self($startDate, $startDate->add(new DateInterval('P1M')));
    }

    /**
     * Creates new instance for a specific ISO8601 week.
     *
     * @param mixed    $int_or_datepoint a year as an int or a datepoint
     * @param int|null $week             index from 1 to 53 included
     */
    public static function createFromISOWeek($int_or_datepoint, int $week = null): self
    {
        if (is_int($int_or_datepoint)) {
            $week = self::filterRange(self::filterInt($week, 'week'), 1, 53);
            $startDate = (new DateTimeImmutable())->setTime(0, 0, 0, 0)
                ->setISODate(self::filterInt($int_or_datepoint, 'year'), $week, 1);

            return new self($startDate, $startDate->add(new DateInterval('P7D')));
        }

        $datepoint = datepoint($int_or_datepoint)->setTime(0, 0, 0, 0);
        $startDate = $datepoint
            ->setISODate((int) $datepoint->format('o'), (int) $datepoint->format('W'), 1);

        return new self($startDate, $startDate->add(new DateInterval('P7D')));
    }

    /**
     * Creates new instance for a specific date.
     *
     * The date is truncated so that the time range starts at midnight
     * according to the date timezone and last a full day.
     */
    public static function createFromDay($datepoint): self
    {
        $startDate = datepoint($datepoint)->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')));
    }

    /**
     * Creates new instance for a specific date and hour.
     *
     * The starting datepoint represents the beginning of the hour
     * The interval is equal to 1 hour
     */
    public static function createFromHour($datepoint): self
    {
        $datepoint = datepoint($datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('PT1H')));
    }

    /**
     * Creates new instance for a specific date, hour and minute.
     *
     * The starting datepoint represents the beginning of the minute
     * The interval is equal to 1 minute
     */
    public static function createFromMinute($datepoint): self
    {
        $datepoint = datepoint($datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), (int) $datepoint->format('i'), 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('PT1M')));
    }

    /**
     * Creates new instance for a specific date, hour, minute and second.
     *
     * The starting datepoint represents the beginning of the second
     * The interval is equal to 1 second
     */
    public static function createFromSecond($datepoint): self
    {
        $datepoint = datepoint($datepoint);
        $startDate = $datepoint->setTime(
            (int) $datepoint->format('H'),
            (int) $datepoint->format('i'),
            (int) $datepoint->format('s'),
            0
        );

        return new self($startDate, $startDate->add(new DateInterval('PT1S')));
    }

    /**
     * Creates new instance for a specific instant.
     */
    public static function createFromDatepoint($datepoint): self
    {
        $datepoint = datepoint($datepoint);

        return new self($datepoint, $datepoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestampInterval(): float
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * @inheritdoc
     */
    public function getDatePeriod($duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, duration($duration), $this->endDate, $option);
    }

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * This method is not part of the Interval interface.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString()
    {
        $period = $this->jsonSerialize();

        return $period['startDate'].'/'.$period['endDate'];
    }

    /**
     * Returns the Json representation of an instance using
     * the JSON representation of dates as returned by Javascript Date.toJSON() method.
     *
     * This method is not part of the Interval interface.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toJSON
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toISOString
     *
     * @return string[]
     */
    public function jsonSerialize()
    {
        static $utc;
        $utc = $utc ?? new DateTimeZone('UTC');

        return [
            'startDate' => $this->startDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
            'endDate' => $this->endDate->setTimezone($utc)->format(self::ISO8601_FORMAT),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function compareDuration(Interval $interval): int
    {
        return $this->endDate <=> $this->startDate->add($interval->getDateInterval());
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     *
     * This method is not part of the Interval interface.
     */
    public function durationGreaterThan(Interval $interval): bool
    {
        return 1 === $this->compareDuration($interval);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     *
     * This method is not part of the Interval interface.
     */
    public function durationLessThan(Interval $interval): bool
    {
        return -1 === $this->compareDuration($interval);
    }

    /**
     * Tells whether the current instance duration is equal to the submitted one.
     *
     * This method is not part of the Interval interface.
     */
    public function sameDurationAs(Interval $interval): bool
    {
        return 0 === $this->compareDuration($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function equalsTo(Interval $interval): bool
    {
        return $this->startDate == $interval->getStartDate()
            && $this->endDate == $interval->getEndDate();
    }

    /**
     * {@inheritdoc}
     */
    public function abuts(Interval $interval): bool
    {
        return $this->startDate == $interval->getEndDate()
            || $this->endDate == $interval->getStartDate();
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(Interval $interval): bool
    {
        return $this->startDate < $interval->getEndDate()
            && $this->endDate > $interval->getStartDate();
    }

    /**
     * {@inheritdoc}
     */
    public function isAfter($index): bool
    {
        if ($index instanceof Interval) {
            return $this->startDate >= $index->getEndDate();
        }

        return $this->startDate > datepoint($index);
    }

    /**
     * {@inheritdoc}
     */
    public function isBefore($index): bool
    {
        if ($index instanceof Interval) {
            return $this->endDate <= $index->getStartDate();
        }

        return $this->endDate <= datepoint($index);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($index): bool
    {
        if ($index instanceof Interval) {
            return $this->containsPeriod($index);
        }

        return $this->containsDatePoint(datepoint($index));
    }

    /**
     * Tells whether the a Interval is fully contained within the current instance.
     */
    private function containsPeriod(Interval $interval): bool
    {
        return $this->containsDatePoint($interval->getStartDate())
            && ($interval->getEndDate() >= $this->startDate && $interval->getEndDate() <= $this->endDate);
    }

    /**
     * Tells whether a datepoint is fully contained within the current instance.
     */
    private function containsDatePoint(DateTimeInterface $datepoint): bool
    {
        return ($datepoint >= $this->startDate && $datepoint < $this->endDate)
            || ($datepoint == $this->startDate && $datepoint == $this->endDate);
    }

    /**
     * @inheritdoc
     *
     * @param DateInterval|Interval|string|int $duration
     */
    public function split($duration): iterable
    {
        $startDate = $this->startDate;
        $duration = duration($duration);
        do {
            $endDate = $startDate->add($duration);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }
            yield new self($startDate, $endDate);

            $startDate = $endDate;
        } while ($startDate < $this->endDate);
    }

    /**
     * @inheritdoc
     *
     * @param DateInterval|Interval|string|int $duration
     */
    public function splitBackwards($duration): iterable
    {
        $endDate = $this->endDate;
        $duration = duration($duration);
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
     * {@inheritdoc}
     */
    public function intersect(Interval $interval): Interval
    {
        if (!$this->overlaps($interval)) {
            throw new Exception(sprintf('Both %s objects should overlaps', Interval::class));
        }

        return new self(
            ($interval->getStartDate() > $this->startDate) ? $interval->getStartDate() : $this->startDate,
            ($interval->getEndDate() < $this->endDate) ? $interval->getEndDate() : $this->endDate
        );
    }

    /**
     * {@inheritdoc}
     */
    public function gap(Interval $interval): Interval
    {
        if ($this->overlaps($interval)) {
            throw new Exception(sprintf('Both %s objects should not overlaps', Interval::class));
        }

        if ($interval->getStartDate() > $this->startDate) {
            return new self($this->endDate, $interval->getStartDate());
        }

        return new self($interval->getEndDate(), $this->startDate);
    }

    /**
     * {@inheritdoc}
     */
    public function diff(Interval $interval): array
    {
        if ($interval->equalsTo($this)) {
            return [null, null];
        }

        $intersect = $this->intersect($interval);
        $merge = $this->merge($interval);
        if ($merge->getStartDate() == $intersect->getStartDate()) {
            return [$merge->startingOn($intersect->getEndDate()), null];
        }

        if ($merge->getEndDate() == $intersect->getEndDate()) {
            return [$merge->endingOn($intersect->getStartDate()), null];
        }

        return [
            $merge->endingOn($intersect->getStartDate()),
            $merge->startingOn($intersect->getEndDate()),
        ];
    }

    /**
     * @inheritdoc
     *
     * @param DateTimeInterface|int|string $datepoint
     */
    public function startingOn($datepoint): Interval
    {
        $startDate = datepoint($datepoint);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate);
    }

    /**
     * @inheritdoc
     *
     * @param DateTimeInterface|int|string $datepoint
     */
    public function endingOn($datepoint): Interval
    {
        $endDate = datepoint($datepoint);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate);
    }

    /**
     * Returns a new instance with a new ending datepoint.
     *
     * This method is not part of the Interval interface.
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function withDurationAfterStart($duration): Interval
    {
        return $this->endingOn($this->startDate->add(duration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint.
     *
     * This method is not part of the Interval interface.
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function withDurationBeforeEnd($duration): Interval
    {
        return $this->startingOn($this->endDate->sub(duration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint
     * moved forward or backward by the given interval.
     *
     * This method is not part of the Interval interface.
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function moveStartDate($duration): Interval
    {
        return $this->startingOn($this->startDate->add(duration($duration)));
    }

    /**
     * Returns a new instance with a new ending datepoint
     * moved forward or backward by the given interval.
     *
     * This method is not part of the Interval interface.
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function moveEndDate($duration): Interval
    {
        return $this->endingOn($this->endDate->add(duration($duration)));
    }

    /**
     * @inheritdoc
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function move($duration): Interval
    {
        $duration = duration($duration);
        $period = new self($this->startDate->add($duration), $this->endDate->add($duration));
        if ($period->equalsTo($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * @inheritdoc
     *
     * @param DateInterval|Interval|int|string $duration
     */
    public function expand($duration): Interval
    {
        $duration = duration($duration);
        $period = new self($this->startDate->sub($duration), $this->endDate->add($duration));
        if ($period->equalsTo($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * Returns the difference between two Interval objects expressed in seconds.
     *
     * This method is not part of the Interval interface.
     */
    public function timestampIntervalDiff(Interval $interval): float
    {
        return $this->getTimestampInterval() - $interval->getTimestampInterval();
    }

    /**
     * Returns the difference between two Interval objects expressed in DateInterval.
     *
     * This method is not part of the Interval interface.
     */
    public function dateIntervalDiff(Interval $interval): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($interval->getDateInterval()));
    }

    /**
     * Merges one or more Interval objects to return a new instance.
     * The resulting instance represents the largest duration possible.
     *
     * This method is not part of the Interval interface.
     *
     * @param Interval ...$intervals
     */
    public function merge(Interval $interval, Interval ...$intervals): Interval
    {
        array_unshift($intervals, $interval);
        $carry = $this;
        foreach ($intervals as $interval) {
            if ($carry->getStartDate() > $interval->getStartDate()) {
                $carry = $carry->startingOn($interval->getStartDate());
            }

            if ($carry->getEndDate() < $interval->getEndDate()) {
                $carry = $carry->endingOn($interval->getEndDate());
            }
        }

        return $carry;
    }
}
