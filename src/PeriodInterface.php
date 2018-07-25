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
     * Tells whether two PeriodInterface share the same datepoints.
     */
    public function equalsTo(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface abuts.
     */
    public function abuts(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface overlaps.
     */
    public function overlaps(PeriodInterface $period): bool;

    /**
     * Tells whether a PeriodInterface is entirely after the specified index.
     * The index can be a DateTimeInterface object or another PeriodInterface object.
     *
     * @param PeriodInterface|DateTimeInterface $index
     */
    public function isAfter($index): bool;

    /**
     * Tells whether a PeriodInterface is entirely before the specified index.
     * The index can be a DateTimeInterface object or another PeriodInterface object.
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
     * Computes the intersection between two PeriodInterface objects.
     *
     * @throws Exception If both objects do not overlaps
     */
    public function intersect(PeriodInterface $period): self;

    /**
     * Computes the gap between two PeriodInterface objects.
     *
     * @throws Exception If both objects overlaps
     */
    public function gap(PeriodInterface $period): self;

    /**
     * Returns an instance with the specified starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     */
    public function startingOn(DateTimeInterface $startDate): self;

    /**
     * Returns an instance with the specified ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     */
    public function endingOn(DateTimeInterface $endDate): self;
}
