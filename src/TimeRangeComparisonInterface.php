<?php

/**
 * This file is part of the Period library.
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/period/
 * @version 2.5.0
 * @package League.Period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

/**
 * TimeRangeMutationInterface defines an interface for comparing a League\Period\TimeRangeInterface
 *
 * @package league.period
 * @since   2.5.0
 */
interface TimeRangeComparisonInterface extends TimeRangeInterface
{
    /**
     * Tells whether two TimeRangeInterface share the same endpoints.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function sameValueAs(TimeRangeInterface $period);

    /**
     * Tells whether two TimeRangeInterface object abuts
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function abuts(TimeRangeInterface $period);

    /**
     * Tells whether two TimeRangeInterface objects overlaps.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function overlaps(TimeRangeInterface $period);

    /**
     * Tells whether a TimeRangeInterface is entirely after the specified index
     *
     * @param \League\Period\TimeRangeInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isAfter($index);

    /**
     * Tells whether a TimeRangeInterface is entirely before the specified index
     *
     * @param \League\Period\TimeRangeInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isBefore($index);

    /**
     * Tells whether the specified index is fully contained within
     * the current TimeRangeInterface object.
     *
     * @param \League\Period\TimeRangeInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function contains($index);

    /**
     * Computes the difference between two overlapsing TimeRangeInterface objects
     * and return an array containing the difference expressed as TimeRangeInterface objects
     * The array will:
     * - be empty if both objects have the same endpoints
     * - contain one Period object if both objects share one endpoint
     * - contain two Period objects if both objects share no endpoint
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @throws \LogicException if both object do not overlaps
     *
     * @return \League\Period\TimeRangeInterface[]
     */
    public function diff(TimeRangeInterface $period);

    /**
     * Returns the difference between two TimeRangeInterface objects.
     *
     * @param \League\Period\TimeRangeInterface $period
     * @param bool                           $get_as_seconds If used and set to true, the method will return
     *                                                       an int which represents the duration in seconds
     *                                                       instead of a\DateInterval object
     *
     * @return \DateInterval|int|double
     */
    public function durationDiff(TimeRangeInterface $period, $get_as_seconds = false);

    /**
     * Compares two TimeRangeInterface objects according to their duration.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return int
     */
    public function compareDuration(TimeRangeInterface $period);

    /**
     * Tells whether the current TimeRangeInterface object duration
     * is greater than the submitted one.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function durationGreaterThan(TimeRangeInterface $period);

    /**
     * Tells whether the current TimeRangeInterface object duration
     * is less than the submitted one.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function durationLessThan(TimeRangeInterface $period);

    /**
     * Tells whether the current TimeRangeInterface object duration
     * is equal to the submitted one
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @return bool
     */
    public function sameDurationAs(TimeRangeInterface $period);
}
