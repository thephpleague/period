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
use function array_unshift;
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
     * Returns the Interval starting datepoint.
     *
     * The starting datepoint is included in the specified period.
     * The starting datepoint is always less than or equal to the ending datepoint.
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Returns the Interval ending datepoint.
     *
     * The ending datepoint is excluded from the specified period.
     * The ending datepoint is always greater than or equal to the starting datepoint.
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Returns the Interval duration as expressed in seconds.
     */
    public function getTimestampInterval(): float
    {
        return $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
    }

    /**
     * Returns the Interval duration as a DateInterval object.
     */
    public function getDateInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the instance.
     *
     * This method is not part of the Interval.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     */
    public function getDatePeriod($duration, int $option = 0): DatePeriod
    {
        return new DatePeriod($this->startDate, duration($duration), $this->endDate, $option);
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
        $period = $this->jsonSerialize();

        return $period['startDate'].'/'.$period['endDate'];
    }

    /**
     * Returns the Json representation of an instance using
     * the JSON representation of dates as returned by Javascript Date.toJSON() method.
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
     * Compares two instances according to their duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current Interval is lesser than the submitted Interval object</li>
     * <li>  1 if the current Interval is greater than the submitted Interval object</li>
     * <li>  0 if both Interval objects have the same duration</li>
     * </ul>
     */
    public function compareDuration(Period $interval): int
    {
        return $this->endDate <=> $this->startDate->add($interval->getDateInterval());
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     */
    public function durationGreaterThan(Period $interval): bool
    {
        return 1 === $this->compareDuration($interval);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     */
    public function durationLessThan(Period $interval): bool
    {
        return -1 === $this->compareDuration($interval);
    }

    /**
     * Tells whether the current instance duration is equal to the submitted one.
     */
    public function sameDurationAs(Period $interval): bool
    {
        return 0 === $this->compareDuration($interval);
    }

    /**
     * Tells whether two Interval share the same datepoints.
     */
    public function equals(Period $interval): bool
    {
        return $this->startDate == $interval->getStartDate()
            && $this->endDate == $interval->getEndDate();
    }

    /**
     * Tells whether two Interval abuts.
     */
    public function abuts(Period $interval): bool
    {
        return $this->startDate == $interval->getEndDate()
            || $this->endDate == $interval->getStartDate();
    }

    /**
     * Tells whether two Interval overlaps.
     */
    public function overlaps(Period $interval): bool
    {
        return $this->startDate < $interval->getEndDate()
            && $this->endDate > $interval->getStartDate();
    }

    /**
     * Tells whether a Interval is entirely after the specified index.
     * The index can be a DateTimeInterface object or another Interval object.
     */
    public function isAfter($index): bool
    {
        if ($index instanceof Period) {
            return $this->startDate >= $index->getEndDate();
        }

        return $this->startDate > datepoint($index);
    }

    /**
     * Tells whether a Interval is entirely before the specified index.
     * The index can be a DateTimeInterface object or another Interval object.
     */
    public function isBefore($index): bool
    {
        if ($index instanceof Period) {
            return $this->endDate <= $index->getStartDate();
        }

        return $this->endDate <= datepoint($index);
    }

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     */
    public function contains($index): bool
    {
        if ($index instanceof Period) {
            return $this->containsPeriod($index);
        }

        return $this->containsDatePoint(datepoint($index));
    }

    /**
     * Tells whether the a Interval is fully contained within the current instance.
     */
    private function containsPeriod(Period $interval): bool
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
     * @param DateInterval|Period|string|int $duration
     *
     * @return Period[]
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
     * @param DateInterval|Period|string|int $duration
     *
     * @return Period[]
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
     * Computes the intersection between two instances.
     *
     * @throws Exception If both objects do not overlaps
     */
    public function intersect(Period $interval): self
    {
        if (!$this->overlaps($interval)) {
            throw new Exception(sprintf('Both %s objects should overlaps', Period::class));
        }

        return new self(
            ($interval->getStartDate() > $this->startDate) ? $interval->getStartDate() : $this->startDate,
            ($interval->getEndDate() < $this->endDate) ? $interval->getEndDate() : $this->endDate
        );
    }

    /**
     * Computes the gap between two instances.
     *
     * @throws Exception If both objects overlaps
     */
    public function gap(Period $interval): self
    {
        if ($this->overlaps($interval)) {
            throw new Exception(sprintf('Both %s objects should not overlaps', Period::class));
        }

        if ($interval->getStartDate() > $this->startDate) {
            return new self($this->endDate, $interval->getStartDate());
        }

        return new self($interval->getEndDate(), $this->startDate);
    }

    /**
     * Computes the difference between two overlapsing instances.
     *
     * This method is not part of the Interval.
     *
     * Returns an array containing the difference expressed as Period objects
     * The array will always contains 2 elements:
     *
     * <ul>
     * <li>an NULL filled array if both objects have the same datepoints</li>
     * <li>one Period object and NULL if both objects share one datepoint</li>
     * <li>two Period objects if both objects share no datepoint</li>
     * </ul>
     *
     * @throws Exception if both objects do not overlaps
     */
    public function diff(Period $interval): array
    {
        if ($interval->equals($this)) {
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
     * Returns an instance with the specified starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     *
     * @param DateTimeInterface|int|string $datepoint
     */
    public function startingOn($datepoint): self
    {
        $startDate = datepoint($datepoint);
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
     *
     * @param DateTimeInterface|int|string $datepoint
     */
    public function endingOn($datepoint): self
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
     * @param DateInterval|Period|int|string $duration
     */
    public function withDurationAfterStart($duration): self
    {
        return $this->endingOn($this->startDate->add(duration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint.
     *
     * @param DateInterval|Period|int|string $duration
     */
    public function withDurationBeforeEnd($duration): self
    {
        return $this->startingOn($this->endDate->sub(duration($duration)));
    }

    /**
     * Returns a new instance with a new starting datepoint
     * moved forward or backward by the given interval.
     *
     * @param DateInterval|Period|int|string $duration
     */
    public function moveStartDate($duration): self
    {
        return $this->startingOn($this->startDate->add(duration($duration)));
    }

    /**
     * Returns a new instance with a new ending datepoint
     * moved forward or backward by the given interval.
     *
     * @param DateInterval|Period|int|string $duration
     */
    public function moveEndDate($duration): self
    {
        return $this->endingOn($this->endDate->add(duration($duration)));
    }

    /**
     * Returns a new instance where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * @param DateInterval|Period|int|string $duration
     */
    public function move($duration): self
    {
        $duration = duration($duration);
        $period = new self($this->startDate->add($duration), $this->endDate->add($duration));
        if ($period->equals($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * substracted from the starting datepoint and added to the ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrinked.
     *
     * @param DateInterval|Period|int|string $duration
     */
    public function expand($duration): self
    {
        $duration = duration($duration);
        $period = new self($this->startDate->sub($duration), $this->endDate->add($duration));
        if ($period->equals($this)) {
            return $this;
        }

        return $period;
    }

    /**
     * Returns the difference between two instances expressed in seconds.
     */
    public function timestampIntervalDiff(Period $interval): float
    {
        return $this->getTimestampInterval() - $interval->getTimestampInterval();
    }

    /**
     * Returns the difference between two instances expressed in DateInterval.
     */
    public function dateIntervalDiff(Period $interval): DateInterval
    {
        return $this->endDate->diff($this->startDate->add($interval->getDateInterval()));
    }

    /**
     * Merges one or more instances to return a new instance.
     * The resulting instance represents the largest duration possible.
     *
     * @param Period ...$intervals
     */
    public function merge(Period $interval, Period ...$intervals): self
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
