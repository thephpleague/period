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

namespace League\Period\Interfaces;

/**
 * TimeRangeInfo defines an interface to information for a time range
 *
 * @package league.period
 * @since   2.5.0
 */
interface TimeRangeInfo extends TimeRange
{
    /**
     * Tells whether two TimeRange share the same endpoints.
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function sameValueAs(TimeRange $period);

    /**
     * Tells whether two TimeRange object abuts
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function abuts(TimeRange $period);

    /**
     * Tells whether two TimeRange objects overlaps.
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function overlaps(TimeRange $period);

    /**
     * Tells whether a TimeRange is entirely after the specified index
     *
     * @param \League\Period\Interfaces\TimeRange|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isAfter($index);

    /**
     * Tells whether a TimeRange is entirely before the specified index
     *
     * @param \League\Period\Interfaces\TimeRange|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function isBefore($index);

    /**
     * Tells whether the specified index is fully contained within
     * the current TimeRange object.
     *
     * @param \League\Period\Interfaces\TimeRange|\DateTimeInterface|\DateTime $index
     *
     * @return bool
     */
    public function contains($index);

    /**
     * Compares two TimeRange objects according to their duration.
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return int
     */
    public function compareDuration(TimeRange $period);

    /**
     * Tells whether the current TimeRange object duration
     * is greater than the submitted one.
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function durationGreaterThan(TimeRange $period);

    /**
     * Tells whether the current TimeRange object duration
     * is less than the submitted one.
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function durationLessThan(TimeRange $period);

    /**
     * Tells whether the current TimeRange object duration
     * is equal to the submitted one
     *
     * @param \League\Period\Interfaces\TimeRange $period
     *
     * @return bool
     */
    public function sameDurationAs(TimeRange $period);
}
