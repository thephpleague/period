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
 * TimeRangeMutationInterface defines an interface for modifying a League\Period\TimeRangeInterface
 *
 * @package league.period
 * @since   2.5.0
 */
interface TimeRangeMutationInterface extends TimeRangeInterface
{
    /**
     * Returns a new TimeRangeMutationInterface object with a new includedd starting endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $start starting included datetime endpoint
     *
     * @throws \LogicException If $start does not permit the creation of a new object
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function startingOn($start);

    /**
     * Returns a new TimeRangeMutationInterface object with a new excluded ending endpoint.
     *
     * @param string|\DateTimeInterface|\DateTime $end ending excluded datetime endpoint
     *
     * @throws \LogicException If $end does not permit the creation of a new object
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function endingOn($end);

    /**
     * Returns a new TimeRangeMutationInterface object with a new excluded ending endpoint.
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function withDuration($duration);

    /**
     * Returns a new TimeRangeMutationInterface object with an added interval
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $duration does not permit the creation of a new object
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function add($duration);

    /**
     * Returns a new TimeRangeMutationInterface object with a Removed interval
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @throws \LogicException If The $duration does not permit the creation of a new object
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function sub($duration);

    /**
     * Returns a new TimeRangeMutationInterface object adjacent to the current TimeRangeMutationInterface
     * and starting with its ending endpoint.
     * If no duration is provided the new TimeRangeMutationInterface will be created
     * using the current object duration
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function next($duration = null);

    /**
     * Returns a new TimeRangeMutationInterface object adjacent to the current TimeRangeMutationInterface
     * and ending with its starting endpoint.
     * If no duration is provided the new TimeRangeMutationInterface will have the
     * same duration as the current one
     *
     * @param \DateInterval|int|string $duration The duration. If an int is passed, it is
     *                                            interpreted as the duration expressed in seconds.
     *                                            If a string is passed, it must be parsable by
     *                                            `DateInterval::createFromDateString`
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function previous($duration = null);

    /**
     * Merges one or more TimeRangeMutationInterface objects to return a new TimeRangeMutationInterface object.
     *
     * The resultant object englobes the largest duration possible.
     *
     * @param \League\Period\TimeRangeInterface $arg,... one or more Period objects
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function merge();

    /**
     * Computes the intersection between two TimeRangeMutationInterface objects.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @throws \LogicException If Both objects do not overlaps
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function intersect(TimeRangeInterface $period);

    /**
     * Computes the gap between two TimeRangeMutationInterface objects.
     *
     * @param \League\Period\TimeRangeInterface $period
     *
     * @throws \LogicException If Both objects overlaps
     *
     * @return \League\Period\TimeRangeMutationInterface
     */
    public function gap(TimeRangeInterface $period);
}
