<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use function filter_var;
use function intdiv;
use const FILTER_VALIDATE_INT;

/**
 * League Period Datepoint.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Datepoint extends DateTimeImmutable
{
    /**
     * Returns a position in time expressed as a DateTimeImmutable object.
     *
     * A datepoint can be
     * <ul>
     * <li>a DateTimeInterface object
     * <li>a integer interpreted as a timestamp
     * <li>a string parsable by DateTime::__construct
     * </ul>
     *
     * @param mixed $datepoint a position in time
     */
    public static function create($datepoint): self
    {
        if ($datepoint instanceof self) {
            return $datepoint;
        }

        if ($datepoint instanceof DateTimeInterface) {
            return new self($datepoint->format('Y-m-d H:i:s.u'), $datepoint->getTimezone());
        }

        if (false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT))) {
            return new self('@'.$timestamp);
        }

        return new self($datepoint);
    }

    public function extractDay(): Period
    {
        $datepoint = $this->setTime(0, 0);

        return new Period($datepoint, $datepoint->add(new DateInterval('P1D')));
    }

    public function extractIsoWeek(): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setISODate((int) $this->format('o'), (int) $this->format('W'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P7D')));
    }

    public function extractMonth(): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (int) $this->format('n'), 1);

        return new Period($startDate, $startDate->add(new DateInterval('P1M')));
    }

    public function extractQuarter(): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (intdiv((int) $this->format('n'), 3) * 3) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P3M')));
    }

    public function extractSemester(): Period
    {
        $startDate = $this
            ->setTime(0, 0)
            ->setDate((int) $this->format('Y'), (intdiv((int) $this->format('n'), 6) * 6) + 1, 1);

        return new Period($startDate, $startDate->add(new DateInterval('P6M')));
    }

    public function extractYear(): Period
    {
        $year = (int) $this->format('Y');
        $datepoint = $this->setTime(0, 0);

        return new Period($datepoint->setDate($year, 1, 1), $datepoint->setDate(++$year, 1, 1));
    }


    public function extractIsoYear(): Period
    {
        $year = (int) $this->format('o');
        $datepoint = $this->setTime(0, 0);

        return new Period($datepoint->setISODate($year, 1, 1), $datepoint->setISODate(++$year, 1, 1));
    }
}
