<?php

/**
 * League.Uri (https://period.thephpleague.com).
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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use TypeError;
use const FILTER_VALIDATE_INT;
use function array_filter;
use function array_reduce;
use function array_values;
use function filter_var;
use function func_num_args;
use function get_class;
use function gettype;
use function intdiv;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;

/**
 * A immutable value object class to manipulate Time Range.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
final class Period implements PeriodInterface, JsonSerializable
{
    private const ISO8601_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /**
     * Period starting included date point.
     *
     * @var DateTimeImmutable
     */
    private $startDate;

    /**
     * Period ending excluded date point.
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
     * Create a new instance.
     *
     * @param mixed $startDate the starting included datepoint
     * @param mixed $endDate   the ending excluded datepoint
     *
     * @throws Exception If $startDate is greater than $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $startDate = self::filterDatePoint($startDate);
        $endDate = self::filterDatePoint($endDate);
        if ($startDate > $endDate) {
            throw new Exception('The ending datepoint must be greater or equal to the starting datepoint');
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Validate the datepoint.
     *
     * The datepoint can be
     *
     * <ul>
     * <li>a DateTimeInterface implementing object</li>
     * <li>a stringable type in a format supported by DateTime::__construct</li>
     * </ul>
     *
     * @param mixed $datepoint a stringable type or a DateTimInterface object
     */
    private static function filterDatePoint($datepoint): DateTimeImmutable
    {
        if ($datepoint instanceof DateTimeImmutable) {
            return $datepoint;
        }

        if ($datepoint instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($datepoint);
        }

        if (is_string($datepoint) || method_exists($datepoint, '__toString')) {
            return new DateTimeImmutable((string) $datepoint);
        }

        throw new TypeError(sprintf(
            'The datepoint must a string or a DateTimeInteface object %s given',
            is_object($datepoint) ? get_class($datepoint) : gettype($datepoint)
        ));
    }

    /**
     * Validate a DateInterval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param mixed $interval an interval
     */
    private static function filterDateInterval($interval): DateInterval
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        }

        if (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        }

        if (is_string($interval) || method_exists($interval, '__toString')) {
            return DateInterval::createFromDateString((string) $interval);
        }

        throw new TypeError(sprintf(
            'The interval must a scalar or a DateInterval object %s given',
            is_object($interval) ? get_class($interval) : gettype($interval)
        ));
    }

    /**
     * Create a Period object from a DatePeriod.
     *
     * @throws Exception If the submitted DatePeriod lacks an end Date.
     *                   This is possible of the DatePeriod was created using
     *                   Recurrences instead of a end date.
     *                   https://secure.php.net/manual/en/dateperiod.getenddate.php
     */
    public static function createFromDatePeriod(DatePeriod $datePeriod): self
    {
        $endDate = $datePeriod->getEndDate();
        if ($endDate instanceof DateTimeInterface) {
            return new self($datePeriod->getStartDate(), $endDate);
        }

        throw new Exception('The submitted DatePeriod object does not contain an end date');
    }

    /**
     * Create a Period object from a starting point and an interval.
     *
     */
    public static function createFromDuration($startDate, $interval): self
    {
        $startDate = self::filterDatePoint($startDate);

        return new self($startDate, $startDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Create a Period object from a ending excluded datepoint and an interval.
     *
     */
    public static function createFromDurationBeforeEnd($endDate, $interval): self
    {
        $endDate = self::filterDatePoint($endDate);

        return new self($endDate->sub(self::filterDateInterval($interval)), $endDate);
    }

    /**
     * Create a Period object for a specific Year.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     */
    public static function createFromYear($int_or_datepoint): self
    {
        $year = filter_var($int_or_datepoint, FILTER_VALIDATE_INT);
        if (false !== $year) {
            $startDate = (new DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P1Y')));
        }

        $datepoint = self::filterDatePoint($int_or_datepoint);
        $startDate = $datepoint->setDate((int) $datepoint->format('Y'), 1, 1)->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * Create a Period object for a specific semester in a given year.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     * @param mixed $semester         a semester index from 1 to 2 included
     */
    public static function createFromSemester($int_or_datepoint, $semester = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($int_or_datepoint);
            $month = (intdiv((int) $datepoint->format('n'), 6) * 6) + 1;
            $startDate = $datepoint
                ->setDate((int) $datepoint->format('Y'), $month, 1)
                ->setTime(0, 0, 0, 0)
            ;

            return new self($startDate, $startDate->add(new DateInterval('P6M')));
        }

        $month = ((self::validateRange(self::filterInt($semester, 'semester'), 1, 2) - 1) * 6) + 1;
        $startDate = (new DateTimeImmutable())
            ->setDate(self::filterInt($int_or_datepoint, 'year'), $month, 1)
            ->setTime(0, 0, 0, 0)
        ;

        return new self($startDate, $startDate->add(new DateInterval('P6M')));
    }

    /**
     * Filter the input integer.
     *
     * @throws Exception if the given value can not be converted to an int.
     */
    private static function filterInt($value, string $name): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (false !== $int) {
            return $int;
        }

        throw new Exception(sprintf('The %s value must be an integer %s given', $name, gettype($value)));
    }

    /**
     * Validate a int according to a range.
     *
     * @param int $value the value to validate
     * @param int $min   the minimum value
     * @param int $max   the maximal value
     *
     * @throws Exception If the value is not in the range
     */
    private static function validateRange(int $value, int $min, int $max): int
    {
        if ($value >= $min && $value <= $max) {
            return $value;
        }

        throw new Exception('the submitted value is not contained within the valid range');
    }

    /**
     * Create a Period object for a specific quarter in a given year.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     * @param mixed $quarter          quarter index from 1 to 4 included
     */
    public static function createFromQuarter($int_or_datepoint, $quarter = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($int_or_datepoint);
            $month = (intdiv((int) $datepoint->format('n'), 3) * 3) + 1;
            $startDate = $datepoint
                ->setDate((int) $datepoint->format('Y'), $month, 1)
                ->setTime(0, 0, 0, 0)
            ;

            return new self($startDate, $startDate->add(new DateInterval('P3M')));
        }

        $month = ((self::validateRange(self::filterInt($quarter, 'quarter'), 1, 4) - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())
            ->setDate(self::filterInt($int_or_datepoint, 'year'), $month, 1)
            ->setTime(0, 0, 0, 0)
        ;

        return new self($startDate, $startDate->add(new DateInterval('P3M')));
    }

    /**
     * Create a Period object for a specific year and month.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     * @param mixed $month            month index from 1 to 12 included
     */
    public static function createFromMonth($int_or_datepoint, $month = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($int_or_datepoint);
            $startDate = $datepoint
                ->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1)
                ->setTime(0, 0, 0, 0)
            ;

            return new self($startDate, $startDate->add(new DateInterval('P1M')));
        }

        $startDate = (new DateTimeImmutable())
            ->setDate(
                self::filterInt($int_or_datepoint, 'year'),
                self::validateRange(self::filterInt($month, 'month'), 1, 12),
                1
            )
            ->setTime(0, 0, 0, 0)
        ;

        return new self($startDate, $startDate->add(new DateInterval('P1M')));
    }

    /**
     * Create a Period object for a specific week.
     *
     * @param mixed $int_or_datepoint a year as an int or a datepoint
     * @param mixed $week             index from 1 to 53 included
     */
    public static function createFromWeek($int_or_datepoint, $week = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($int_or_datepoint);
            $startDate = $datepoint
                ->sub(new DateInterval('P'.((int) $datepoint->format('N') - 1).'D'))
                ->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P1W')));
        }

        $startDate = (new DateTimeImmutable())
            ->setISODate(
                self::filterInt($int_or_datepoint, 'year'),
                self::validateRange(self::filterInt($week, 'week'), 1, 53)
            )
            ->setTime(0, 0, 0, 0);
        ;

        return new self($startDate, $startDate->add(new DateInterval('P1W')));
    }

    /**
     * Create a Period object for a specific date.
     *
     * The date is truncated so that the time range starts at midnight
     * according to the date timezone and last a full day.
     */
    public static function createFromDay($datepoint): self
    {
        $startDate = self::filterDatePoint($datepoint)->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1D')));
    }

    /**
     * Create a Period object for a specific date and hour.
     *
     * The starting datepoint represents the beginning of the hour
     * The interval is equal to 1 hour
     */
    public static function createFromHour($datepoint): self
    {
        $datepoint = self::filterDatePoint($datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('PT1H')));
    }

    /**
     * Create a Period object for a specific date, hour and minute.
     *
     * The starting datepoint represents the beginning of the minute
     * The interval is equal to 1 minute
     */
    public static function createFromMinute($datepoint): self
    {
        $datepoint = self::filterDatePoint($datepoint);
        $startDate = $datepoint->setTime((int) $datepoint->format('H'), (int) $datepoint->format('i'), 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('PT1M')));
    }

    /**
     * Create a Period object for a specific date, hour, minute and second.
     *
     * The starting datepoint represents the beginning of the second
     * The interval is equal to 1 second
     */
    public static function createFromSecond($datepoint): self
    {
        $datepoint = self::filterDatePoint($datepoint);
        $startDate = $datepoint->setTime(
            (int) $datepoint->format('H'),
            (int) $datepoint->format('i'),
            (int) $datepoint->format('s'),
            0
        );

        return new self($startDate, $startDate->add(new DateInterval('PT1S')));
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
     * {@inheritdoc}
     */
    public function getDatePeriod($interval, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, self::filterDateInterval($interval), $this->endDate, $option);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $period = $this->jsonSerialize();

        return $period['startDate'].'/'.$period['endDate'];
    }

    /**
     * Returns the Json representation of a Period object using
     * the JSON representation of dates as returned by Javascript Date.toJSON() method.
     *
     * This method is not part of the PeriodInterface.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toJSON
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
    public function compareDuration(PeriodInterface $period): int
    {
        return $this->endDate <=> $this->startDate->add($period->getDateInterval());
    }

    /**
     * Tells whether the current Period object duration
     * is greater than the submitted one.
     *
     * This method is not part of the PeriodInterface.
     */
    public function durationGreaterThan(PeriodInterface $period): bool
    {
        return 1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is less than the submitted one.
     *
     * This method is not part of the PeriodInterface.
     */
    public function durationLessThan(PeriodInterface $period): bool
    {
        return -1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is equal to the submitted one.
     *
     * This method is not part of the PeriodInterface.
     */
    public function sameDurationAs(PeriodInterface $period): bool
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * {@inheritdoc}
     */
    public function equalsTo(PeriodInterface $period): bool
    {
        return $this->startDate == $period->getStartDate()
            && $this->endDate == $period->getEndDate();
    }

    /**
     * DEPRECATION WARNING! This methid will be removed in the next majoir point release.
     *
     * @deprecated deprecated since version 4.0
     * @see PeriodInterface::equalsTo
     */
    public function sameValueAs(PeriodInterface $period): bool
    {
        return $this->equalsTo($period);
    }

    /**
     * {@inheritdoc}
     */
    public function abuts(PeriodInterface $period): bool
    {
        return $this->startDate == $period->getEndDate()
            || $this->endDate == $period->getStartDate();
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(PeriodInterface $period): bool
    {
        return !$this->abuts($period)
            && $this->startDate < $period->getEndDate()
            && $this->endDate > $period->getStartDate();
    }

    /**
     * {@inheritdoc}
     */
    public function isAfter($index): bool
    {
        if ($index instanceof PeriodInterface) {
            return $this->startDate >= $index->getEndDate();
        }

        return $this->startDate > self::filterDatePoint($index);
    }

    /**
     * {@inheritdoc}
     */
    public function isBefore($index): bool
    {
        if ($index instanceof PeriodInterface) {
            return $this->endDate <= $index->getStartDate();
        }

        return $this->endDate <= self::filterDatePoint($index);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($index): bool
    {
        if ($index instanceof PeriodInterface) {
            return $this->containsPeriod($index);
        }

        return $this->containsDatePoint(self::filterDatePoint($index));
    }

    /**
     * Tells whether a Period object is fully contained within
     * the current Period object.
     */
    private function containsPeriod(PeriodInterface $period): bool
    {
        return $this->containsDatePoint($period->getStartDate())
            && ($period->getEndDate() >= $this->startDate && $period->getEndDate() <= $this->endDate);
    }

    /**
     * Tells whether a datepoint is fully contained within
     * the current Period object.
     */
    private function containsDatePoint(DateTimeInterface $datepoint): bool
    {
        return ($datepoint >= $this->startDate && $datepoint < $this->endDate)
            || ($datepoint == $this->startDate && $datepoint == $this->endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function split($interval): iterable
    {
        $startDate = $this->startDate;
        $interval = self::filterDateInterval($interval);
        do {
            $endDate = $startDate->add($interval);
            if ($endDate > $this->endDate) {
                $endDate = $this->endDate;
            }
            yield new self($startDate, $endDate);

            $startDate = $endDate;
        } while ($startDate < $this->endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function splitBackwards($interval): iterable
    {
        $endDate = $this->endDate;
        $interval = self::filterDateInterval($interval);
        do {
            $startDate = $endDate->sub($interval);
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
    public function intersect(PeriodInterface $period): PeriodInterface
    {
        if (!$this->overlaps($period)) {
            throw new Exception(sprintf('Both %s  object should overlaps', PeriodInterface::class));
        }

        return new self(
            ($period->getStartDate() > $this->startDate) ? $period->getStartDate() : $this->startDate,
            ($period->getEndDate() < $this->endDate) ? $period->getEndDate() : $this->endDate
        );
    }

    /**
     * {@inheritdoc}
     */
    public function gap(PeriodInterface $period): PeriodInterface
    {
        if ($this->overlaps($period)) {
            throw new Exception(sprintf('Both %s  object should not overlaps', PeriodInterface::class));
        }

        if ($period->getStartDate() > $this->startDate) {
            return new self($this->endDate, $period->getStartDate());
        }

        return new self($period->getEndDate(), $this->startDate);
    }

    /**
     * {@inheritdoc}
     */
    public function startingOn($datepoint): PeriodInterface
    {
        $startDate = self::filterDatePoint($datepoint);
        if ($startDate == $this->startDate) {
            return $this;
        }

        return new self($startDate, $this->endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function endingOn($datepoint): PeriodInterface
    {
        $endDate = self::filterDatePoint($datepoint);
        if ($endDate == $this->endDate) {
            return $this;
        }

        return new self($this->startDate, $endDate);
    }

    /**
     * Returns a new Period object with a new ending date point.
     *
     * This method is not part of the PeriodInterface.
     */
    public function withDuration($interval): PeriodInterface
    {
        return $this->endingOn($this->startDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a new starting date point.
     *
     * This method is not part of the PeriodInterface.
     */
    public function withDurationBeforeEnd($interval): PeriodInterface
    {
        return $this->startingOn($this->endDate->sub(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a new starting date point
     * moved forward or backward by the given interval.
     *
     * This method is not part of the PeriodInterface.
     */
    public function moveStartDate($interval): PeriodInterface
    {
        return $this->startingOn($this->startDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a new ending date point
     * moved forward or backward by the given interval.
     *
     * This method is not part of the PeriodInterface.
     */
    public function moveEndDate($interval): PeriodInterface
    {
        return $this->endingOn($this->endDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method is not part of the PeriodInterface.
     */
    public function move($interval): PeriodInterface
    {
        $interval = self::filterDateInterval($interval);
        $period = new self($this->startDate->add($interval), $this->endDate->add($interval));
        if ($period->equalsTo($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * Returns a new Period object where the given DateInterval is
     * substracted from the starting datepoint and added to the ending datepoint.
     * Depending on the DateInterval value, the resulting PeriodInterface duration
     * will be expanded or shrinked.
     *
     * This method is not part of the PeriodInterface.
     */
    public function expand($interval): PeriodInterface
    {
        $interval = self::filterDateInterval($interval);

        $period = new self($this->startDate->sub($interval), $this->endDate->add($interval));
        if ($period->equalsTo($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * Merges one or more Period objects to return a new Period object.
     * The resultant object represents the largest duration possible.
     *
     * This method is not part of the PeriodInterface.
     *
     * @param PeriodInterface ...$periods
     */
    public function merge(PeriodInterface ...$periods): PeriodInterface
    {
        return array_reduce($periods, [$this, 'reducer'], $this);
    }

    /**
     * Returns a Period whose endpoints are the largest possible
     * between 2 instance of Period objects.
     */
    private function reducer(PeriodInterface $carry, PeriodInterface $period): PeriodInterface
    {
        if ($carry->getStartDate() > $period->getStartDate()) {
            $carry = $carry->startingOn($period->getStartDate());
        }

        if ($carry->getEndDate() < $period->getEndDate()) {
            $carry = $carry->endingOn($period->getEndDate());
        }

        return $carry;
    }

    /**
     * Returns the difference between two Period objects expressed in seconds.
     *
     * This method is not part of the PeriodInterface.
     */
    public function timestampIntervalDiff(PeriodInterface $period): float
    {
        return $this->getTimestampInterval() - $period->getTimestampInterval();
    }

    /**
     * Returns the difference between two Period objects expressed in DateInterval.
     *
     * This method is not part of the PeriodInterface.
     */
    public function dateIntervalDiff(PeriodInterface $period): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($period->getDateInterval()));
    }

    /**
     * Computes the difference between two overlapsing Period objects.
     *
     * This method is not part of the PeriodInterface.
     *
     * Returns a array containing the difference expressed as Period objects
     * The array will:
     *
     * <ul>
     * <li>be empty if both objects have the same datepoints</li>
     * <li>contain one Period object if both objects share one datepoint</li>
     * <li>contain two Period objects if both objects share no datepoint</li>
     * </ul>
     *
     * @throws Exception if both object do not overlaps
     */
    public function diff(PeriodInterface $period): array
    {
        if (!$this->overlaps($period)) {
            throw new Exception('Both Period objects must overlaps');
        }

        $res = [
            self::createFromDatepoints($this->startDate, $period->getStartDate()),
            self::createFromDatepoints($this->endDate, $period->getEndDate()),
        ];

        $filter = function (PeriodInterface $period) {
            return $period->getStartDate() != $period->getEndDate();
        };

        return array_values(array_filter($res, $filter));
    }

    /**
     * Create a new instance given two datepoints.
     *
     * The datepoints will be used as to allow the creation of
     * a Period object
     */
    private static function createFromDatepoints($datepoint1, $datepoint2): self
    {
        $startDate = self::filterDatePoint($datepoint1);
        $endDate = self::filterDatePoint($datepoint2);
        if ($startDate > $endDate) {
            return new self($endDate, $startDate);
        }

        return new self($startDate, $endDate);
    }
}
