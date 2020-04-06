<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LeagueTest\Period\StaticAnalysis;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use League\Period\Period;

/**
 * This is a static analysis fixture to verify that the API signature
 * of a period allows for pure operations. Almost all methods will seem to be
 * redundant or trivial: that's normal, we're just verifying the
 * transitivity of immutable type signatures.
 *
 * Please note that this does not guarantee that the internals of the period
 * library are pure/safe, but just that the declared API to the outside world
 * is seen as immutable.
 */
final class PeriodIsImmutable
{
    /** @psalm-pure */
    public static function pureConstructor(): Period
    {
        return new Period(
            new DateTimeImmutable('yesterday'),
            new DateTimeImmutable('tomorrow'),
        );
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     */
    public static function pureStaticPeriodApi(): array
    {
        $startDate = new DateTimeImmutable('yesterday');
        $endDate = new DateTimeImmutable('tomorrow');
        $duration = DateInterval::createFromDateString('2 days');

        $interval = [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return [
            Period::__set_state($interval),
            Period::after($startDate, $duration),
            Period::before($endDate, $duration),
            Period::around($startDate, $duration),
            Period::fromYear(1900),
            Period::fromIsoYear(1900),
            Period::fromSemester(1900),
            Period::fromQuarter(1900),
            Period::fromMonth(1900),
            Period::fromIsoWeek(1900),
            Period::fromDay(1900),
        ];
    }

    /** @psalm-pure */
    public static function PeriodFromDatePeriodIsPure(DatePeriod $datePeriod): Period
    {
        return Period::fromDatePeriod($datePeriod);
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureGetters(Period $a): array
    {
        return [
            $a->getStartDate(),
            $a->getEndDate(),
            $a->getBoundaryType(),
            $a->getTimestampInterval(),
            $a->getDateInterval(),
            $a->__toString(),
            $a->toIso8601(),
            $a->jsonSerialize(),
            $a->format('Y-m-d'),
            $a->isStartIncluded(),
            $a->isStartExcluded(),
            $a->isEndIncluded(),
            $a->isEndExcluded(),
        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureComparisonRelationAndManipulation(Period $a, Period $b): array
    {
        return [
            $a->durationCompare($b),
            $a->durationEquals($b),
            $a->durationGreaterThan($b),
            $a->durationLessThan($b),
            $a->isBefore($b),
            $a->bordersOnStart($b),
            $a->isStartedBy($b),
            $a->isDuring($b),
            $a->contains($b),
            $a->equals($b),
            $a->isEndedBy($b),
            $a->bordersOnEnd($b),
            $a->isAfter($b),
            $a->abuts($b),
            $a->overlaps($b),
            $a->timestampIntervalDiff($b),
            $a->dateIntervalDiff($b),
        ];
    }
}
