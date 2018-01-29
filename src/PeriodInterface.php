<?php
/**
 * League.Period (http://period.thephpleague.com)
 *
 * @package   League.period
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2014-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/period/
 */

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;

/**
 * A PHP Interface to represent an time range.
 *
 * Inspired from JodaTime ReadableInterval Interface
 * @see http://www.joda.org/joda-time/apidocs/org/joda/time/ReadableInterval.html
 *
 * Inspired from Period
 * @see https://github.com/thephpleague/period/blob/master/src/Period.php
 */
interface PeriodInterface extends JsonSerializable
{
    /**
     * Returns the PeriodInterface starting datepoint.
     *
     * The starting datepoint is included in the specified period.
     * The starting datepoint is always less than or equal to the ending datepoint.
     *
     * @return DateTimeImmutable
     */
    public function getStartDate(): DateTimeImmutable;

    /**
     * Returns the PeriodInterface ending datepoint.
     *
     * The ending datepoint is excluded from the specified period.
     * The ending datepoint is always greater than or equal to the starting datepoint.
     *
     * @return DateTimeImmutable
     */
    public function getEndDate(): DateTimeImmutable;

    /**
     * Returns the PeriodInterface duration as expressed in seconds.
     *
     * @return float
     */
    public function getTimestampInterval(): float;

    /**
     * Returns the PeriodInterface duration as a DateInterval object.
     *
     * @return DateInterval
     */
    public function getDateInterval(): DateInterval;

    /**
     * Allows iteration over a set of dates and times,
     * recurring at regular intervals, over the PeriodInterface object.
     *
     * @param DateInterval $interval The interval
     *
     * @param int $option can be set to DatePeriod::EXCLUDE_START_DATE
     *                    to exclude the start date from the set of
     *                    recurring dates within the period.
     *
     * @return DatePeriod
     */
    public function getDatePeriod(DateInterval $interval, int $option = 0): DatePeriod;

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
     * @param DateInterval $interval The interval
     *
     * @return Generator
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
     * @param DateInterval $interval The interval
     *
     * @return Generator
     */
    public function splitBackwards(DateInterval $interval): iterable;

    /**
     * Compares two PeriodInterface objects according to their duration.
     *
     * Returns:
     * <ul>
     * <li> -1 if the current PeriodInterface is lesser than the submitted PeriodInterface object</li>
     * <li>  1 if the current PeriodInterface is greater than the submitted PeriodInterface object</li>
     * <li>  0 if both PeriodInterface objects have the same duration</li>
     * </ul>
     *
     * @param PeriodInterface $period
     *
     * @return int
     */
    public function compareDuration(PeriodInterface $period): int;

    /**
     * Tells whether two PeriodInterface share the same datepoints.
     *
     * @param PeriodInterface $period
     *
     * @return bool
     */
    public function sameValueAs(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface object abuts
     *
     * @param PeriodInterface $period
     *
     * @return bool
     */
    public function abuts(PeriodInterface $period): bool;

    /**
     * Tells whether two PeriodInterface objects overlaps
     *
     * @param PeriodInterface $period
     *
     * @return bool
     */
    public function overlaps(PeriodInterface $period): bool;

    /**
     * Tells whether a PeriodInterface is entirely after the specified index
     *
     * @param PeriodInterface|DateTimeInterface $index
     *
     * @return bool
     */
    public function isAfter($index): bool;

    /**
     * Tells whether a PeriodInterface is entirely before the specified index
     *
     * @param PeriodInterface|DateTimeInterface $index
     *
     * @return bool
     */
    public function isBefore($index): bool;

    /**
     * Tells whether the specified index is fully contained within
     * the current Period object.
     *
     * @param PeriodInterface|DateTimeInterface $index
     *
     * @return bool
     */
    public function contains($index): bool;

    /**
     * Returns the string representation of a Period object
     * as a string in the ISO8601 interval format
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the Json representation of a Period object using
     * the JSON representation of dates as returned by Javascript Date.toJSON() method
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toJSON
     *
     * @return string[]
     */
    public function jsonSerialize();
}