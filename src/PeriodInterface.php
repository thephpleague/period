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
use DateTimeImmutable;
use DateTimeInterface;

/**
 * A PHP Interface to represent an time range.
 *
 * Inspired from JodaTime ReadableInterval Interface
 * @see http://www.joda.org/joda-time/apidocs/org/joda/time/ReadableInterval.html
 *
 * Inspired from Period
 * @see https://github.com/thephpleague/period/blob/master/src/Period.php
 */
interface PeriodInterface
{
    /**
     * Returns the PeriodInterface starting datepoint.
     *
     * The starting datepoint is included in the specified period.
     * The starting datepoint is always less than or equal to the ending datepoint.
     */
    public function getStartDate(): DateTimeImmutable;

    /**
     * Returns the PeriodInterface ending datepoint.
     *
     * The ending datepoint is excluded from the specified period.
     * The ending datepoint is always greater than or equal to the starting datepoint.
     */
    public function getEndDate(): DateTimeImmutable;

    /**
     * Returns the PeriodInterface duration as expressed in seconds.
     */
    public function getTimestampInterval(): float;

    /**
     * Returns the PeriodInterface duration as a DateInterval object.
     */
    public function getDateInterval(): DateInterval;

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the PeriodInterface object.
     *
     * @see http://php.net/manual/en/dateperiod.construct.php
     */
    public function getDatePeriod(DateInterval $interval, int $option = 0): DatePeriod;

    /**
     * Tells whether two PeriodInterface share the same datepoints.
     */
    public function equalsTo(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface object abuts.
     */
    public function abuts(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface objects overlaps.
     */
    public function overlaps(PeriodInterface $period): bool;

    /**
     * Tells whether a PeriodInterface is entirely after the specified index.
     *
     * @param PeriodInterface|DateTimeInterface $index
     */
    public function isAfter($index): bool;

    /**
     * Tells whether a PeriodInterface is entirely before the specified index.
     *
     * @param PeriodInterface|DateTimeInterface $index
     */
    public function isBefore($index): bool;

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param PeriodInterface|DateTimeInterface $index
     */
    public function contains($index): bool;

    /**
     * Compares two PeriodInterface objects according to their duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current PeriodInterface is lesser than the submitted PeriodInterface object</li>
     * <li>  1 if the current PeriodInterface is greater than the submitted PeriodInterface object</li>
     * <li>  0 if both PeriodInterface objects have the same duration</li>
     * </ul>
     */
    public function compareDuration(PeriodInterface $period): int;

    /**
     * Returns the string representation as a ISO8601 interval format.
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns an instance with the specified starting date point.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting date point.
     */
    public function startingOn(DateTimeInterface $startDate): PeriodInterface;

    /**
     * Returns an instance with the specified ending date point.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending date point.
     */
    public function endingOn(DateTimeInterface $endDate): PeriodInterface;

    /**
     * Computes the intersection between two PeriodInterface objects.
     *
     * @throws Exception If Both objects do not overlaps
     */
    public function intersect(PeriodInterface $period): PeriodInterface;

    /**
     * Computes the gap between two PeriodInterface objects.
     *
     * @throws Exception If Both objects overlaps
     */
    public function gap(PeriodInterface $period): PeriodInterface;

    /**
     * Allows splitting a PeriodInterface in smaller PeriodInterface objects according
     * to a given interval.
     *
     * The returned iterable PeriodInterface set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the starting datepoint of the parent object.</li>
     * <li>The last returned object MUST share the ending datepoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST start immediately after the previously returned object</li>
     * </ul>
     *
     * @return PeriodInterface[]
     */
    public function split(DateInterval $interval): iterable;

    /**
     * Allows splitting a PeriodInterface in smaller PeriodInterface object according
     * to a given interval.
     *
     * The returned iterable PeriodInterface set is ordered so that:
     * <ul>
     * <li>The first returned object MUST share the ending datepoint of the parent object.</li>
     * <li>The last returned object MUST share the starting datepoint of the parent object.</li>
     * <li>The last returned object MUST have a duration equal or lesser than the submitted interval.</li>
     * <li>All returned objects except for the first one MUST end immediately before the previously returned object</li>
     * </ul>
     *
     * @return PeriodInterface[]
     */
    public function splitBackwards(DateInterval $interval): iterable;
}
