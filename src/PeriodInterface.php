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
 * A value object class to manipulate Time Range.
 */
interface PeriodInterface
{
    /**
     * Returns the starting DateTime.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getStart();

    /**
     * Returns the ending DateTime.
     *
     * @return \DateTimeInterface|\DateTime
     */
    public function getEnd();

    /**
     * Returns the PeriodInterface duration as a DateInterval object.
     *
     * @param bool $get_as_seconds If used and set to true, the method will return an int which
     *                             represents the duration in seconds instead of a \DateInterval
     *                             object.
     *
     * @return \DateInterval|int|double
     */
    public function getDuration($get_as_seconds = false);

    /**
     * Returns a list of Datetime objects included in the Period according to a given interval.
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \DatePeriod
     */
    public function getRange($interval);

    /**
     * String representation of a PeriodInterface using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString();

    /**
     * Tells whether two PeriodInterface share the same endpoints.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function sameValueAs(PeriodInterface $period);

    /**
     * Tells whether two PeriodInterface object abuts
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function abuts(PeriodInterface $period);

    /**
     * Tells whether two PeriodInterface objects overlaps.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function overlaps(PeriodInterface $period);

    /**
     * Tells whether a PeriodInterface is entirely after the specified index
     *
     * @param \League\Period\PeriodInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isAfter($index);

    /**
     * Tells whether a PeriodInterface is entirely before the specified index
     *
     * @param \League\Period\PeriodInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isBefore($index);

    /**
     * Tells whether the specified index is fully contained within
     * the current PeriodInterface object.
     *
     * @param \League\Period\PeriodInterface|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function contains($index);

    /**
     * Computes the difference between two overlapsing PeriodInterface objects
     * and return an array containing the difference expressed as PeriodInterface objects
     * The array will:
     * - be empty if both objects have the same endpoints
     * - contain one Period object if both objects share one endpoint
     * - contain two Period objects if both objects share no endpoint
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @throws \LogicException
     *
     * @return \League\Period\PeriodInterface[]
     */
    public function diff(PeriodInterface $period);

    /**
     * Returns the difference between two PeriodInterface objects.
     *
     * @param \League\Period\PeriodInterface $period
     * @param bool                           $get_as_seconds If used and set to true, the method will return
     *                                                       an int which represents the duration in seconds
     *                                                       instead of a\DateInterval object
     *
     * @return \DateInterval|int|double
     */
    public function durationDiff(PeriodInterface $period, $get_as_seconds = false);

    /**
     * Compares two PeriodInterface objects according to their duration.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return int
     */
    public function compareDuration(PeriodInterface $period);

    /**
     * Tells whether the given PeriodInterface duration is less than the current PeriodInterface object.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function durationGreaterThan(PeriodInterface $period);

    /**
     * Tells whether the given PeriodInterface duration is greater than the current PeriodInterface object.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function durationLessThan(PeriodInterface $period);

    /**
     * Tells whether the given PeriodInterface duration is equals to the current PeriodInterface object.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @return bool
     */
    public function sameDurationAs(PeriodInterface $period);

    /**
     * Returns a new PeriodInterface object with a new includedd starting endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $start starting included datetime endpoint
     *
     * @return \League\Period\PeriodInterface
     */
    public function startingOn($start);

    /**
     * Returns a new PeriodInterface object with a new excluded ending endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $end ending excluded datetime endpoint
     *
     * @return \League\Period\PeriodInterface
     */
    public function endingOn($end);

    /**
     * Returns a new PeriodInterface object with a new ending DateTime.
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\PeriodInterface
     */
    public function withDuration($duration);

    /**
     * Adds an interval to the current PeriodInterface object
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\PeriodInterface
     */
    public function add($duration);

    /**
     * Removes an interval to the current PeriodInterface object.
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\PeriodInterface
     */
    public function sub($duration);

    /**
     * Returns a new PeriodInterface object adjacent to the current PeriodInterface
     * and starting with its ending endpoint.
     * If no duration is provided the new PeriodInterface will be created
     * using the current object duration
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\PeriodInterface
     */
    public function next($duration = null);

    /**
     * Returns a new PeriodInterface object adjacent to the current PeriodInterface
     * and ending with its starting endpoint.
     * If no duration is provided the new PeriodInterface will have the
     * same duration as the current one
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\PeriodInterface
     */
    public function previous($duration = null);

    /**
     * Merges one or more PeriodInterface objects to return a new PeriodInterface object.
     *
     * The resultant object englobes the largest duration possible.
     *
     * @param \League\Period\PeriodInterface $arg,... one or more Period objects
     *
     * @return \League\Period\PeriodInterface
     */
    public function merge();

    /**
     * Computes the intersection between two PeriodInterface objects.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @throws \LogicException
     *
     * @return \League\Period\PeriodInterface
     */
    public function intersect(PeriodInterface $period);

    /**
     * Computes the gap between two PeriodInterface objects.
     *
     * @param \League\Period\PeriodInterface $period
     *
     * @throws \LogicException
     *
     * @return \League\Period\PeriodInterface
     */
    public function gap(PeriodInterface $period);
}
