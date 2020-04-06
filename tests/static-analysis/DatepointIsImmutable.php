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

use DateTime;
use League\Period\Datepoint;
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
final class DatepointIsImmutable
{
    /**
     * @return Datepoint[]|bool[]
     *
     * @psalm-pure
     */
    public static function pureStaticDatepointApi(string $datepoint): array
    {
        return [
            Datepoint::create($datepoint),
            Datepoint::createFromFormat('Y-m-d', $datepoint),
        ];
    }

    /** @psalm-pure */
    public static function pureDatePointFromMutable(DateTime $dateTime): Datepoint
    {
        return Datepoint::createFromMutable($dateTime);
    }

    /**
     * @return Period[]
     *
     * @psalm-pure
     */
    public static function pureIntervalConstructors(Datepoint $a): array
    {
        return [
            $a->getSecond(),
            $a->getMinute(),
            $a->getHour(),
            $a->getDay(),
            $a->getIsoWeek(),
            $a->getMonth(),
            $a->getQuarter(),
            $a->getSemester(),
            $a->getYear(),
            $a->getIsoYear(),

        ];
    }

    /**
     * @return bool[]
     *
     * @psalm-pure
     */
    public static function pureRelationMethods(Datepoint $a, Period $b): array
    {
        return [
            $a->isBefore($b),
            $a->bordersOnStart($b),
            $a->isStarting($b),
            $a->isDuring($b),
            $a->isEnding($b),
            $a->bordersOnEnd($b),
            $a->abuts($b),
            $a->isAfter($b),
        ];
    }
}
