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
 * TimeRangeInterface defines an interface for interacting with time range
 *
 * @package league.period
 * @since   2.5.0
 */
interface TimeRangeInterface
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
     * Returns the TimeRangeInterface duration as a DateInterval object.
     *
     * @param bool $get_as_seconds If used and set to true, the method will return an int which
     *                             represents the duration in seconds instead of a \DateInterval
     *                             object.
     *
     * @return \DateInterval|int|double
     */
    public function getDuration($get_as_seconds = false);

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the TimeRangeInterface object.
     *
     * @param \DateInterval|int|string $interval The interval. If an int is passed, it is
     *                                           interpreted as the duration expressed in seconds.
     *                                           If a string is passed, it must be parsable by
     *                                           `DateInterval::createFromDateString`
     *
     * @return \DatePeriod
     */
    public function getDatePeriod($interval);

    /**
     * String representation of a TimeRangeInterface using ISO8601 Time interval format
     *
     * @return string
     */
    public function __toString();
}
