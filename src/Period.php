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
use function sprintf;

/**
 * A immutable value object class to manipulate Time Range.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
final class Period implements PeriodInterface
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
     * @param mixed $startDate starting included date point
     * @param mixed $endDate   ending excluded date point
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
     * Validate the DateTimeInterface.
     *
     * @param mixed $datepoint
     *
     * @return DateTimeImmutable
     */
    private static function filterDatePoint($datepoint): DateTimeImmutable
    {
        if ($datepoint instanceof DateTimeImmutable) {
            return $datepoint;
        }

        if ($datepoint instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($datepoint);
        }

        if (is_scalar($datepoint) || method_exists($datepoint, '__toString')) {
            return new DateTimeImmutable((string) $datepoint);
        }

        throw new TypeError(sprintf(
            'The datepoint must a scalar or a DateTimeInteface object %s given',
            is_object($datepoint) ? get_class($datepoint) : gettype($datepoint)
        ));
    }

    /**
     * Create a Period object from a DatePeriod.
     *
     * @param DatePeriod $datePeriod
     *
     * @throws Exception If the submitted DatePeriod lacks an End DateTimeInterface
     *
     * @return self
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
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateTimeInterface|string $startDate The start date point
     * @param mixed                    $interval  The interval
     *
     * @return self
     */
    public static function createFromDuration($startDate, $interval): self
    {
        $startDate = self::filterDatePoint($startDate);

        return new self($startDate, $startDate->add(self::filterDateInterval($interval)));
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
     * @param mixed $interval The interval
     *
     * @return DateInterval
     */
    private static function filterDateInterval($interval): DateInterval
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        }

        if (false !== ($res = filter_var($interval, FILTER_VALIDATE_INT))) {
            return new DateInterval('PT'.$res.'S');
        }

        if (is_scalar($interval) || method_exists($interval, '__toString')) {
            return DateInterval::createFromDateString((string) $interval);
        }

        throw new TypeError(sprintf(
            'The interval must a scalar or a DateInterval object %s given',
            is_object($interval) ? get_class($interval) : gettype($interval)
        ));
    }

    /**
     * Create a Period object from a ending excluded datepoint and an interval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateTimeInterface|string $endDate  The start date point
     * @param mixed                    $interval The interval
     *
     * @return self
     */
    public static function createFromDurationBeforeEnd($endDate, $interval): self
    {
        $endDate = self::filterDatePoint($endDate);

        return new self($endDate->sub(self::filterDateInterval($interval)), $endDate);
    }

    /**
     * Create a Period object for a specific Year.
     *
     * @param mixed $year
     *
     * @return self
     */
    public static function createFromYear($year): self
    {
        $intYear = filter_var($year, FILTER_VALIDATE_INT);
        if (false !== $intYear) {
            $startDate = new DateTimeImmutable($intYear.'-01-01 00:00:00');

            return new self($startDate, $startDate->add(new DateInterval('P1Y')));
        }

        $datepoint = self::filterDatePoint($year);
        $startDate = $datepoint->setDate((int) $datepoint->format('Y'), 1, 1)->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1Y')));
    }

    /**
     * Create a Period object for a specific semester in a given year.
     *
     * @param mixed $year
     * @param int   $semester Semester Index from 1 to 2
     *
     * @return self
     */
    public static function createFromSemester($year, ?int $semester = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($year);
            $month = (intdiv((int) $datepoint->format('n'), 6) * 6) + 1;
            $startDate = $datepoint->setDate((int) $datepoint->format('Y'), $month, 1)->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P6M')));
        }

        $intYear = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $intYear) {
            throw new TypeError(sprintf('The year value must be an integer %s given', gettype($year)));
        }

        if (null === $semester) {
            throw new Exception('The semester value is missing');
        }

        $month = ((self::validateRange($semester, 1, 2) - 1) * 6) + 1;
        $startDate = new DateTimeImmutable($intYear.'-'.sprintf("%'.02d", $month).'-01');

        return new self($startDate, $startDate->add(new DateInterval('P6M')));
    }

    /**
     * Validate a int according to a range.
     *
     * @param int $value the value to validate
     * @param int $min   the minimum value
     * @param int $max   the maximal value
     *
     * @throws Exception If the value is not in the range
     *
     * @return int
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
     * @param mixed $year
     * @param int   $quarter Quarter Index from 1 to 4
     *
     * @return self
     */
    public static function createFromQuarter($year, ?int $quarter = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($year);
            $month = (intdiv((int) $datepoint->format('n'), 3) * 3) + 1;
            $startDate = $datepoint->setDate((int) $datepoint->format('Y'), $month, 1)->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P3M')));
        }

        $intYear = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $intYear) {
            throw new TypeError(sprintf('The year value must be an integer %s given', gettype($year)));
        }

        if (null === $quarter) {
            throw new Exception('The quarter value is missing');
        }

        $month = ((self::validateRange($quarter, 1, 4) - 1) * 3) + 1;
        $startDate = (new DateTimeImmutable())->setDate($intYear, $month, 1)->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P3M')));
    }

    /**
     * Create a Period object for a specific year and month.
     *
     * @param mixed $year
     * @param int   $month Month index from 1 to 12
     *
     * @return self
     */
    public static function createFromMonth($year, ?int $month = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($year);
            $startDate = $datepoint
                ->setDate((int) $datepoint->format('Y'), (int) $datepoint->format('n'), 1)
                ->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P1M')));
        }

        $intYear = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $intYear) {
            throw new TypeError(sprintf('The year value must be an integer %s given', gettype($year)));
        }

        if (null === $month) {
            throw new Exception('The month value is missing');
        }

        $startDate = (new DateTimeImmutable())
            ->setDate($intYear, self::validateRange($month, 1, 12), 1)
            ->setTime(0, 0, 0, 0);

        return new self($startDate, $startDate->add(new DateInterval('P1M')));
    }

    /**
     * Create a Period object for a specific week.
     *
     * @param mixed $year
     * @param int   $week index from 1 to 53
     *
     * @return self
     */
    public static function createFromWeek($year, ?int $week = null): self
    {
        if (1 === func_num_args()) {
            $datepoint = self::filterDatePoint($year);
            $startDate = $datepoint
                ->sub(new DateInterval('P'.((int) $datepoint->format('N') - 1).'D'))
                ->setTime(0, 0, 0, 0);

            return new self($startDate, $startDate->add(new DateInterval('P1W')));
        }

        $intYear = filter_var($year, FILTER_VALIDATE_INT);
        if (false === $intYear) {
            throw new TypeError(sprintf('The year value must be an integer %s given', gettype($year)));
        }

        if (null === $week) {
            throw new Exception('The week value is missing');
        }

        $startDate = (new DateTimeImmutable())
            ->setISODate($intYear, self::validateRange($week, 1, 53))
            ->setTime(0, 0, 0, 0);
        ;

        return new self($startDate, $startDate->add(new DateInterval('P1W')));
    }

    /**
     * Create a Period object for a specific date.
     *
     * The date is truncated so that the time range starts at midnight
     * according to the date timezone and last a full day.
     *
     * @param DateTimeInterface|string $datepoint
     *
     * @return self
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
     * The Period interval is equal to 1 hour
     *
     * @param DateTimeInterface|string $datepoint
     *
     * @return self
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
     * The Period interval is equal to 1 minute
     *
     * @param DateTimeInterface|string $datepoint
     *
     * @return self
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
     * The Period interval is equal to 1 second
     *
     * @param DateTimeInterface|string $datepoint
     *
     * @return self
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
    public function getDatePeriod($interval, ?int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, self::filterDateInterval($interval), $this->endDate, $option);
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
    public function __toString()
    {
        $period = $this->jsonSerialize();

        return $period['startDate'].'/'.$period['endDate'];
    }

    /**
     * {@inheritdoc}
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
     * @param Period $period
     *
     * @return bool
     */
    public function durationGreaterThan(PeriodInterface $period): bool
    {
        return 1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is less than the submitted one.
     *
     * @param Period $period
     *
     * @return bool
     */
    public function durationLessThan(PeriodInterface $period): bool
    {
        return -1 === $this->compareDuration($period);
    }

    /**
     * Tells whether the current Period object duration
     * is equal to the submitted one.
     *
     * @param Period $period
     *
     * @return bool
     */
    public function sameDurationAs(PeriodInterface $period): bool
    {
        return 0 === $this->compareDuration($period);
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(PeriodInterface $period): bool
    {
        return $this->startDate == $period->getStartDate()
            && $this->endDate == $period->getEndDate();
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
     *
     * @param PeriodInterface $period
     *
     * @return bool
     */
    private function containsPeriod(PeriodInterface $period): bool
    {
        return $this->containsDatePoint($period->getStartDate())
            && ($period->getEndDate() >= $this->startDate && $period->getEndDate() <= $this->endDate);
    }

    /**
     * Tells whether a datepoint is fully contained within
     * the current Period object.
     *
     * @param DateTimeInterface $datepoint
     *
     * @return bool
     */
    private function containsDatePoint(DateTimeInterface $datepoint): bool
    {
        return ($datepoint >= $this->startDate && $datepoint < $this->endDate)
            || ($datepoint == $this->startDate && $datepoint == $this->endDate);
    }

    /**
     * Returns a new Period object with a new included starting date point.
     *
     * @param DateTimeInterface|string $startDate date point
     *
     * @return self
     */
    public function startingOn($startDate): self
    {
        return new self(self::filterDatePoint($startDate), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending date point.
     *
     * @param DateTimeInterface|string $endDate date point
     *
     * @return self
     */
    public function endingOn($endDate): self
    {
        return new self($this->startDate, self::filterDatePoint($endDate));
    }

    /**
     * Returns a new Period object with a new ending date point.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return self
     */
    public function withDuration($interval): self
    {
        return new self($this->startDate, $this->startDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object with a new starting date point.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return self
     */
    public function withDurationBeforeEnd($interval): self
    {
        return new self($this->endDate->sub(self::filterDateInterval($interval)), $this->endDate);
    }

    /**
     * Returns a new Period object with a new starting date point
     * moved forward or backward by the given interval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return self
     */
    public function moveStartDate($interval): self
    {
        return new self($this->startDate->add(self::filterDateInterval($interval)), $this->endDate);
    }

    /**
     * Returns a new Period object with a new ending date point
     * moved forward or backward by the given interval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return self
     */
    public function moveEndDate($interval): self
    {
        return new self($this->startDate, $this->endDate->add(self::filterDateInterval($interval)));
    }

    /**
     * Returns a new Period object where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * The interval can be
     * <ul>
     * <li>a DateInterval object</li>
     * <li>an int interpreted as the duration expressed in seconds.</li>
     * <li>a string in a format supported by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param DateInterval|int|string $interval The interval
     *
     * @return self
     */
    public function move($interval): self
    {
        $interval = self::filterDateInterval($interval);

        return new self($this->startDate->add($interval), $this->endDate->add($interval));
    }

    /**
     * Merges one or more Period objects to return a new Period object.
     *
     * The resultant object represents the largest duration possible.
     *
     * @param Period ...$periods one or more Period objects
     *
     * @return self
     */
    public function merge(PeriodInterface ...$periods): PeriodInterface
    {
        return array_reduce($periods, [$this, 'reducer'], $this);
    }

    /**

     * Returns a Period whose endpoints are the largest possible
     * between 2 instance of Period objects.
     *
     * @param Period $carry
     * @param Period $period
     *
     * @return self
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
     * Computes the intersection between two Period objects.
     *
     * @param Period $period
     *
     * @throws Exception If Both objects do not overlaps
     *
     * @return self
     */
    public function intersect(Period $period): self
    {
        if (!$this->overlaps($period)) {
            throw new Exception('Both object should at least overlaps');
        }

        return new self(
            ($period->getStartDate() > $this->startDate) ? $period->getStartDate() : $this->startDate,
            ($period->getEndDate() < $this->endDate) ? $period->getEndDate() : $this->endDate
        );
    }

    /**
     * Computes the gap between two Period objects.
     *
     * @param Period $period
     *
     * @return self
     */
    public function gap(Period $period): self
    {
        if ($period->getStartDate() > $this->startDate) {
            return new self($this->endDate, $period->getStartDate());
        }

        return new self($period->getEndDate(), $this->startDate);
    }

    /**
     * Returns the difference between two Period objects expressed in seconds.
     *
     * @param Period $period
     *
     * @return float
     */
    public function timestampIntervalDiff(PeriodInterface $period): float
    {
        return $this->getTimestampInterval() - $period->getTimestampInterval();
    }

    /**
     * Returns the difference between two Period objects expressed in DateInterval.
     *
     * @param Period $period
     *
     * @return DateInterval
     */
    public function dateIntervalDiff(PeriodInterface $period): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($period->getDateInterval()));
    }

    /**
     * Computes the difference between two overlapsing Period objects.
     *
     * Returns an array containing the difference expressed as Period objects
     * The array will:
     *
     * <ul>
     * <li>be empty if both objects have the same datepoints</li>
     * <li>contain one Period object if both objects share one datepoint</li>
     * <li>contain two Period objects if both objects share no datepoint</li>
     * </ul>
     *
     * @param Period $period
     *
     * @throws Exception if both object do not overlaps
     *
     * @return Period[]
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
     *
     * @param DateTimeInterface|string $datePoint1 datepoint
     * @param DateTimeInterface|string $datePoint2 datepoint
     *
     * @return Period
     */
    private static function createFromDatepoints($datePoint1, $datePoint2): self
    {
        $startDate = self::filterDatePoint($datePoint1);
        $endDate = self::filterDatePoint($datePoint2);
        if ($startDate > $endDate) {
            return new self($endDate, $startDate);
        }

        return new self($startDate, $endDate);
    }
}
