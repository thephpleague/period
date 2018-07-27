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
use DateTimeImmutable;
use DateTimeInterface;

/**
 * A PHP Interface to represent an time interval.
 *
 * Inspired from JodaTime ReadableInterval Interface
 * @see http://www.joda.org/joda-time/apidocs/org/joda/time/ReadableInterval.html
 *
 * Inspired from Period
 * @see https://github.com/thephpleague/period/blob/master/src/Period.php
 */
interface Interval
{
    /**
     * Returns the Interval starting datepoint.
     *
     * The starting datepoint is included in the specified period.
     * The starting datepoint is always less than or equal to the ending datepoint.
     */
    public function getStartDate(): DateTimeImmutable;

    /**
     * Returns the Interval ending datepoint.
     *
     * The ending datepoint is excluded from the specified period.
     * The ending datepoint is always greater than or equal to the starting datepoint.
     */
    public function getEndDate(): DateTimeImmutable;

    /**
     * Returns the Interval duration as expressed in seconds.
     */
    public function getTimestampInterval(): float;

    /**
     * Returns the Interval duration as a DateInterval object.
     */
    public function getDateInterval(): DateInterval;

    /**
     * Tells whether two Interval share the same datepoints.
     */
    public function equalsTo(Interval $interval): bool;

    /**
     * Tells whether two Interval abuts.
     */
    public function abuts(Interval $interval): bool;

    /**
     * Tells whether two Interval overlaps.
     */
    public function overlaps(Interval $interval): bool;

    /**
     * Tells whether a Interval is entirely after the specified index.
     * The index can be a DateTimeInterface object or another Interval object.
     *
     * @param Interval|DateTimeInterface $index
     */
    public function isAfter($index): bool;

    /**
     * Tells whether a Interval is entirely before the specified index.
     * The index can be a DateTimeInterface object or another Interval object.
     *
     * @param Interval|DateTimeInterface $index
     */
    public function isBefore($index): bool;

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param Interval|DateTimeInterface $index
     */
    public function contains($index): bool;

    /**
     * Compares two Interval objects according to their duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current Interval is lesser than the submitted Interval object</li>
     * <li>  1 if the current Interval is greater than the submitted Interval object</li>
     * <li>  0 if both Interval objects have the same duration</li>
     * </ul>
     */
    public function compareDuration(Interval $interval): int;

    /**
     * Computes the intersection between two Interval objects.
     *
     * @throws Exception If both objects do not overlaps
     */
    public function intersect(Interval $interval): self;

    /**
     * Computes the gap between two Interval objects.
     *
     * @throws Exception If both objects overlaps
     */
    public function gap(Interval $interval): self;

    /**
     * Returns an instance with the specified starting datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified starting datepoint.
     */
    public function startingOn(DateTimeInterface $datepoint): self;

    /**
     * Returns an instance with the specified ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified ending datepoint.
     */
    public function endingOn(DateTimeInterface $datepoint): self;

    /**
     * Returns a new instance where the datepoints
     * are moved forwards or backward simultaneously by the given DateInterval.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     */
    public function move(DateInterval $duration): self;

    /**
     * Returns an instance where the given DateInterval is simultaneously
     * substracted from the starting datepoint and added to the ending datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified new datepoints.
     *
     * Depending on the duration value, the resulting instance duration will be expanded or shrinked.
     */
    public function expand(DateInterval $duration): self;
}
