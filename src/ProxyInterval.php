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

/**
 * A Proxy to ease adding more methods to an Interval implementing object.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class ProxyInterval implements Interval
{
    /**
     * @var Interval
     */
    protected $interval;

    /**
     * New instance.
     */
    public function __construct(Interval $interval)
    {
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->interval->getStartDate();
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->interval->getEndDate();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestampInterval(): float
    {
        return $this->interval->getTimestampInterval();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateInterval(): DateInterval
    {
        return $this->interval->getDateInterval();
    }

    /**
     * {@inheritdoc}
     */
    public function getDatePeriod($duration, int $option = 0): DatePeriod
    {
        return $this->interval->getDatePeriod($duration, $option);
    }

    /**
     * {@inheritdoc}
     */
    public function compareDuration($interval): int
    {
        return $this->interval->compareDuration($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function equalsTo($interval): bool
    {
        return $this->interval->equalsTo($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function abuts($interval): bool
    {
        return $this->interval->abuts($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps($interval): bool
    {
        return $this->interval->overlaps($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function isAfter($index): bool
    {
        return $this->interval->isAfter($index);
    }

    /**
     * {@inheritdoc}
     */
    public function isBefore($index): bool
    {
        return $this->interval->isBefore($index);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($index): bool
    {
        return $this->interval->contains($index);
    }

    /**
     * {@inheritdoc}
     */
    public function split($duration): iterable
    {
        return $this->interval->split($duration);
    }

    /**
     * {@inheritdoc}
     */
    public function splitBackwards($duration): iterable
    {
        return $this->interval->splitBackwards($duration);
    }

    /**
     * {@inheritdoc}
     */
    public function intersect($interval): Interval
    {
        return $this->interval->intersect($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function gap($interval): Interval
    {
        return $this->interval->gap($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function startingOn($datepoint): Interval
    {
        $newInterval = $this->interval->startingOn($datepoint);
        if ($newInterval->equalsTo($this->interval)) {
            return $this;
        }

        $clone = clone $this;
        $clone->interval = $newInterval;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function endingOn($datepoint): Interval
    {
        $newInterval = $this->interval->endingOn($datepoint);
        if ($newInterval->equalsTo($this->interval)) {
            return $this;
        }

        $clone = clone $this;
        $clone->interval = $newInterval;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function move($duration): Interval
    {
        $newInterval = $this->interval->move($duration);
        if ($newInterval->equalsTo($this->interval)) {
            return $this;
        }

        $clone = clone $this;
        $clone->interval = $newInterval;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function expand($duration): Interval
    {
        $newInterval = $this->interval->expand($duration);
        if ($newInterval->equalsTo($this->interval)) {
            return $this;
        }

        $clone = clone $this;
        $clone->interval = $newInterval;

        return $clone;
    }
}
