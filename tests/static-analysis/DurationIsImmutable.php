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

use DateTimeImmutable;
use League\Period\Duration;

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
final class DurationIsImmutable
{
    /** @psalm-pure */
    public static function pureConstructor(): Duration
    {
        return new Duration('1 year');
    }

    /**
     * @return Duration[]|bool[]
     *
     * @psalm-pure
     */
    public static function pureStaticDurationApi(string $duration): array
    {
        return [
            Duration::create($duration),
            Duration::createFromDateString($duration),
        ];
    }

    /**
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureStringRepresentation(Duration $a): string
    {
        return $a->__toString();
    }

    /**
     * @return Duration[]|bool[]
     *
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureAdjustment(Duration $a, DateTimeImmutable $reference_date): array
    {
        return [
            $a->withoutCarryOver($reference_date),
            $a->adjustedTo($reference_date),
        ];
    }
}
