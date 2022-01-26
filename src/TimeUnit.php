<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

enum TimeUnit
{
    case SECOND;
    case MINUTE;
    case HOUR;
    case DAY;
    case ISO_WEEK;
    case MONTH;
    case QUARTER;
    case SEMESTER;
    case YEAR;
    case ISO_YEAR;

    public function snap(Period $period): Period
    {
        $startDate = DatePoint::fromDate($period->startDate);
        $endDate = DatePoint::fromDate($period->endDate);
        return match ($this) {
            self::SECOND => $period->startingOn($startDate->second()->startDate)->endingOn($endDate->second()->endDate),
            self::MINUTE => $period->startingOn($startDate->minute()->startDate)->endingOn($endDate->minute()->endDate),
            self::HOUR => $period->startingOn($startDate->hour()->startDate)->endingOn($endDate->hour()->endDate),
            self::DAY => $period->startingOn($startDate->day()->startDate)->endingOn($endDate->day()->endDate),
            self::ISO_WEEK => $period->startingOn($startDate->isoWeek()->startDate)->endingOn($endDate->isoWeek()->endDate),
            self::MONTH => $period->startingOn($startDate->month()->startDate)->endingOn($endDate->month()->endDate),
            self::QUARTER => $period->startingOn($startDate->quarter()->startDate)->endingOn($endDate->quarter()->endDate),
            self::SEMESTER => $period->startingOn($startDate->semester()->startDate)->endingOn($endDate->semester()->endDate),
            self::YEAR => $period->startingOn($startDate->year()->startDate)->endingOn($endDate->year()->endDate),
            self::ISO_YEAR => $period->startingOn($startDate->isoYear()->startDate)->endingOn($endDate->isoYear()->endDate),
        };
    }
}
