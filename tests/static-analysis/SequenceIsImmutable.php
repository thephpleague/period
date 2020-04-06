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

use League\Period\Period;
use League\Period\Sequence;

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
final class SequenceIsImmutable
{
    /** @psalm-pure */
    public static function pureConstructor(Period $a, Period $b): Sequence
    {
        return new Sequence($a, $b);
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureGetters(Sequence $a): array
    {
        $a->clear();

        return [
            $a->boundaries(),
            $a->gaps(),
            $a->intersections(),
            $a->unions(),
            $a->getBoundaries(),
            $a->getIntersections(),
            $a->getGaps(),
            $a->getTotalTimestampInterval(),
            $a->toArray(),
            $a->jsonSerialize(),
            $a->getIterator(),
            $a->count(),
            $a->isEmpty(),

        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     * @psalm-suppress DeprecatedMethod
     */
    public static function pureCalculations(Sequence $a, Sequence $b): array
    {
        return [
            $a->substract($b),
            $a->subtract($b),
        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     */
    public static function purePeriodCalculations(Sequence $a, Period $b): array
    {
        $a->unshift($b);
        $a->push($b);

        return [
            $a->contains($b),
            $a->indexOf($b),
        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     */
    public static function purePredicateSatisfaction(Sequence $a, callable $predicate): array
    {
        return [
            $a->some($predicate),
            $a->every($predicate),
            $a->sort($predicate),
            $a->filter($predicate),
            $a->sorted($predicate),
            $a->map($predicate),
            $a->reduce($predicate),
        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-pure
     */
    public static function pureOffset(Sequence $a, int $offset): array
    {
        $a->offsetUnset($offset);

        return [
            $a->offsetExists($offset),
            $a->offsetGet($offset),
            $a->get($offset),
            $a->remove($offset),
        ];
    }

    /** @psalm-pure */
    public static function pureOffsetSet(Sequence $a, int $offset, Period $interval): void
    {
        $a->offsetSet($offset, $interval);
        $a->insert($offset, $interval);
        $a->set($offset, $interval);
    }
}
