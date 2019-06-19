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

namespace LeagueTest\Period;

use DateTimeImmutable;
use League\Period\Datepoint;
use League\Period\InvalidIndex;
use League\Period\Period;
use League\Period\Sequence;
use TypeError;
use function json_encode;

/**
 * @coversDefaultClass League\Period\Sequence
 */
final class SequenceTest extends TestCase
{
    public function testIsEmpty(): void
    {
        $sequence = new Sequence();
        self::assertTrue($sequence->isEmpty());
        self::assertCount(0, $sequence);
        self::assertNull($sequence->getBoundaries());
        $sequence->push(Period::fromDay(2012, 6, 23));
        self::assertFalse($sequence->isEmpty());
        self::assertCount(1, $sequence);
        self::assertInstanceOf(Period::class, $sequence->getBoundaries());
    }

    public function testConstructor(): void
    {
        $sequence = new Sequence(Period::fromDay(2012, 6, 23), Period::fromDay(2012, 6, 23));
        self::assertCount(2, $sequence);
        foreach ($sequence as $event) {
            self::assertInstanceOf(Period::class, $event);
        }
    }

    public function testRemove(): void
    {
        $event1 = Period::fromDay(2012, 6, 23);
        $event2 = Period::fromDay(2012, 6, 23);
        $sequence = new Sequence($event1, $event2);
        self::assertSame($event1, $sequence->remove(0));
        self::assertTrue($sequence->contains($event1));
        self::assertCount(1, $sequence);
        self::assertSame($event2, $sequence->remove(0));
        self::assertCount(0, $sequence);
        self::assertFalse($sequence->contains($event2));
        self::expectException(InvalidIndex::class);
        $sequence->remove(1);
    }

    public function testGetter(): void
    {
        $event1 = Period::fromDay(2012, 6, 23);
        $event2 = Period::fromDay(2012, 6, 23);
        $event3 = Period::fromDay(2012, 6, 25);
        $sequence = new Sequence($event1, $event2);
        self::assertInstanceOf(Period::class, $sequence->getBoundaries());
        self::assertTrue($sequence->contains($event2));
        self::assertTrue($sequence->contains($event1));
        self::assertTrue($sequence->contains(Period::fromDay(2012, 6, 23)));
        self::assertSame($event2, $sequence->get(1));
        self::assertSame(0, $sequence->indexOf(Period::fromDay(2012, 6, 23)));
        self::assertFalse($sequence->indexOf(Period::fromDay(2014, 6, 23)));
        $sequence->push($event3);
        self::assertCount(3, $sequence);
        self::assertSame(2, $sequence->indexOf($event3));
        $sequence->unshift(Period::fromDay(2018, 8, 8));
        self::assertCount(4, $sequence);
        self::assertTrue(Period::fromDay(2018, 8, 8)->equals($sequence->get(0)));
        $sequence->clear();
        self::assertTrue($sequence->isEmpty());
        self::assertNull($sequence->getBoundaries());
    }

    public function testGetThrowsException(): void
    {
        self::expectException(InvalidIndex::class);
        (new Sequence())->get(3);
    }

    public function testSetter(): void
    {
        $sequence = new Sequence(Datepoint::create('2011-06-23')->getDay(), Datepoint::create('2011-06-23')->getDay());
        $sequence->set(0, Datepoint::create('2011-06-23')->getDay());
        self::assertEquals(Datepoint::create('2011-06-23')->getDay(), $sequence->get(0));
        $sequence->set(1, Period::fromDay(2012, 6, 23));
        $sequence->set(0, Datepoint::create('2013-06-23')->getDay());
        self::assertEquals(Period::fromDay(2012, 6, 23), $sequence->get(1));
        self::assertEquals(Datepoint::create('2013-06-23')->getDay(), $sequence->get(0));
        self::expectException(InvalidIndex::class);
        $sequence->set(3, Datepoint::create('2013-06-23')->getDay());
    }

    public function testInsert(): void
    {
        $sequence = new Sequence();
        $sequence->insert(0, Datepoint::create('2010-06-23')->getDay());
        self::assertCount(1, $sequence);
        $sequence->insert(1, Datepoint::create('2011-06-24')->getDay());
        self::assertCount(2, $sequence);
        $sequence->insert(-1, Datepoint::create('2012-06-25')->getDay());
        self::assertCount(3, $sequence);
        self::assertTrue(Datepoint::create('2012-06-25')->getDay()->equals($sequence->get(1)));
        self::expectException(InvalidIndex::class);
        $sequence->insert(42, Datepoint::create('2011-06-23')->getDay());
    }

    public function testJsonSerialize(): void
    {
        $day = Datepoint::create('2010-06-23')->getDay();
        self::assertSame('['.json_encode($day).']', json_encode(new Sequence($day)));
    }

    public function testFilterReturnsNewInstance(): void
    {
        $sequence =new Sequence(Period::fromDay(2012, 6, 23), Datepoint::create('2012-06-12')->getDay());

        $filter = function (Period $period) {
            return $period->getStartDate() == new DateTimeImmutable('2012-06-23');
        };

        $newCollection = $sequence->filter($filter);

        self::assertNotEquals($newCollection, $sequence);
        self::assertCount(1, $newCollection);
    }


    public function testFilterReturnsSameInstance(): void
    {
        $sequence = new Sequence(Period::fromDay(2012, 6, 23), Datepoint::create('2012-06-12')->getDay());

        $filter = static function (Period $interval) {
            return true;
        };

        self::assertSame($sequence, $sequence->filter($filter));
    }

    public function testSortedReturnsSameInstance(): void
    {
        $sequence = new Sequence(Period::fromDay(2012, 6, 23), Datepoint::create('2012-06-12')->getDay());
        $sort = function (Period $event1, Period $event2) {
            return 0;
        };

        self::assertSame($sequence, $sequence->sorted($sort));
    }

    public function testSortedReturnsNewInstance(): void
    {
        $sequence = new Sequence(Period::fromMonth(2012, 6), Period::fromDay(2012, 6, 23), Period::fromIsoWeek(2018, 3));
        $sort = static function (Period $event1, Period $event2) {
            return $event1->durationCompare($event2);
        };

        self::assertNotSame($sequence, $sequence->sorted($sort));
    }

    public function testSort(): void
    {
        $day1 = Period::fromDay(2012, 6, 23);
        $day2 = Datepoint::create('2012-06-12')->getDay();
        $sequence = new Sequence($day1, $day2);
        self::assertSame([0 => $day1, 1 => $day2], $sequence->toArray());
        $compare = static function (Period $period1, Period $period2): int {
            return $period1->getStartDate() <=> $period2->getStartDate();
        };
        $sequence->sort($compare);
        self::assertSame([1 => $day2, 0 => $day1], $sequence->toArray());
    }

    public function testSome(): void
    {
        $interval = Period::after('2012-02-01 12:00:00', '1 HOUR');
        $predicate = static function (Period $event) use ($interval) {
            return $interval->overlaps($event);
        };
        $sequence = new Sequence(
            Datepoint::create('2012-02-01')->getDay(),
            Datepoint::create('2013-02-01')->getDay(),
            Datepoint::create('2014-02-01')->getDay()
        );
        self::assertTrue($sequence->some($predicate));
        self::assertFalse((new Sequence())->some($predicate));
    }

    public function testEvery(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2012-02-01')->getDay(),
            Datepoint::create('2013-02-01')->getDay(),
            Datepoint::create('2014-02-01')->getDay()
        );

        $interval = Period::after('2012-01-01', '5 YEARS');
        $predicate = function (Period $event) use ($interval) {
            return $interval->contains($event);
        };

        self::assertTrue($sequence->every($predicate));
        self::assertFalse((new Sequence())->every($predicate));
        self::assertFalse((new Sequence(Datepoint::create('1988-02-01')->getDay()))->every($predicate));
    }

    /**
     * Substract test 1.
     *
     *  [-------------)      [------------)
     *                   -
     *       [--)         [---------------------)
     *                   =
     *  [----)   [----)
     */
    public function testSubstract1(): void
    {
        $sequenceA = new Sequence(
            new Period('2000-01-01', '2000-01-10'),
            new Period('2000-01-12', '2000-01-20')
        );
        $sequenceB = new Sequence(
            new Period('2000-01-05', '2000-01-08'),
            new Period('2000-01-11', '2000-01-25')
        );
        $diff = $sequenceA->substract($sequenceB);

        self::assertCount(2, $diff);
        self::assertSame('[2000-01-01, 2000-01-05)', $diff->get(0)->format('Y-m-d'));
        self::assertSame('[2000-01-08, 2000-01-10)', $diff->get(1)->format('Y-m-d'));
    }

    /**
     * Substract test 2.
     *
     *  [------)      [------)      [------)
     *                   -
     *  [------------------------------------------)
     *                   =
     *  ()
     */
    public function testSubstract2(): void
    {
        $sequenceA = new Sequence(
            new Period('2000-01-01', '2000-01-05'),
            new Period('2000-01-10', '2000-01-15'),
            new Period('2000-01-20', '2000-01-25')
        );
        $sequenceB = new Sequence(
            new Period('2000-01-01', '2000-01-30')
        );
        $diff = $sequenceA->substract($sequenceB);

        self::assertCount(0, $diff);
    }

    /**
     * Substract test 3.
     */
    public function testSubstract3(): void
    {
        $sequenceA = new Sequence(
            new Period('2000-01-01', '2000-01-10'),
            new Period('2000-01-12', '2000-01-20')
        );
        $sequenceB = new Sequence();

        $diff1 = $sequenceA->substract($sequenceB);
        self::assertCount(2, $diff1);
        self::assertSame('[2000-01-01, 2000-01-10)', $diff1->get(0)->format('Y-m-d'));
        self::assertSame('[2000-01-12, 2000-01-20)', $diff1->get(1)->format('Y-m-d'));

        $diff2 = $sequenceB->substract($sequenceA);
        self::assertCount(0, $diff2);
    }

    /**
     * Substract test 4.
     */
    public function testSubstract4(): void
    {
        $sequenceA = new Sequence(
            new Period('2000-01-01', '2000-01-10'),
            new Period('2000-01-12', '2000-01-20')
        );
        $sequenceB = new Sequence(new Period('2003-01-12', '2003-01-20'));
        self::assertSame($sequenceA, $sequenceA->substract($sequenceB));
    }

    /**
     * Substract test 5.
     */
    public function testSubstract5(): void
    {
        $sequenceA = new Sequence(
            new Period('2000-01-01', '2000-01-10'),
            new Period('2001-01-01', '2001-01-10')
        );
        $sequenceB = new Sequence(new Period('2000-01-01', '2000-01-10'));
        self::assertCount(0, $sequenceB->substract($sequenceA));
    }

    /**
     * Intersections test 1.
     *
     *               [------------)
     *                    [--)
     *                    [-------)
     *
     *                 =
     *
     *                    [--)
     */
    public function testGetIntersections1(): void
    {
        $sequence = new Sequence(
            new Period('2018-01-01', '2018-01-31'),
            new Period('2018-01-10', '2018-01-15'),
            new Period('2018-01-10', '2018-01-31')
        );
        $intersections = $sequence->getIntersections();
        self::assertCount(1, $intersections);
        self::assertSame('[2018-01-10, 2018-01-15)', $intersections->get(0)->format('Y-m-d'));
    }

    /**
     * Intersections test 2.
     *
     *        [--------)
     *                     [--)
     *                            [------)
     *               [---------------)
     *
     *                 =
     *
     *               [-)   [--)   [--)
     */
    public function testGetIntersections2(): void
    {
        $sequence = new Sequence(
            new Period('2018-01-01', '2018-01-31'),
            new Period('2018-02-10', '2018-02-20'),
            new Period('2018-03-01', '2018-03-31'),
            new Period('2018-01-20', '2018-03-10')
        );
        $intersections = $sequence->getIntersections();
        self::assertCount(3, $intersections);
        self::assertSame('[2018-01-20, 2018-01-31)', $intersections->get(0)->format('Y-m-d'));
        self::assertSame('[2018-02-10, 2018-02-20)', $intersections->get(1)->format('Y-m-d'));
        self::assertSame('[2018-03-01, 2018-03-10)', $intersections->get(2)->format('Y-m-d'));
    }

    /**
     * gaps test 1.
     *
     *           [--)
     *                    [----)
     *        [-------)
     *
     *                 =
     *
     *                [---)
     */
    public function testGaps1(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2018-11-29')->getDay(),
            Period::after('2018-11-29 + 7 DAYS', '1 DAY'),
            Period::around('2018-11-29', '4 DAYS')
        );

        $gaps = $sequence->getGaps();
        self::assertCount(1, $gaps);
        self::assertSame('[2018-12-03, 2018-12-06)', $gaps->get(0)->format('Y-m-d'));
    }

    /**
     * gaps test 2.
     *
     * No gaps expected
     *
     *          [--)
     *         [----)
     */
    public function testGaps2(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2018-11-29')->getDay(),
            Period::around('2018-11-29', '4 DAYS')
        );

        $gaps = $sequence->getGaps();
        self::assertTrue($gaps->isEmpty());
    }

    public function testUnionReturnsSameInstance(): void
    {
        $sequence = new Sequence(Datepoint::create('2018-11-29')->getDay());
        self::assertSame($sequence, $sequence->unions());
    }

    public function testUnion(): void
    {
        $sequence = new Sequence(
            Datepoint::create('2018-11-29')->getYear(),
            Datepoint::create('2018-11-29')->getMonth(),
            Period::around('2016-06-01', '3 MONTHS')
        );

        $unions = $sequence->unions();
        self::assertEquals($sequence->getBoundaries(), $unions->getBoundaries());
        self::assertTrue($unions->getIntersections()->isEmpty());
        self::assertEquals($sequence->getGaps(), $unions->getGaps());
        self::assertTrue(Period::around('2016-06-01', '3 MONTHS')->equals($unions->get(0)));
        self::assertTrue(Datepoint::create('2018-11-29')->getYear()->equals($unions->get(1)));
    }

    public function testMap(): void
    {
        $sequence = new Sequence(
            Period::fromMonth(2018, 1),
            Period::fromDay(2018, 1, 1)
        );

        $newSequence = $sequence->map(function (Period $period, int $offset): Period {
            if (1 === $offset) {
                return $period;
            }

            return $period->startingOn('2018-01-15');
        });

        self::assertSame($newSequence->get(1), $sequence->get(1));
        self::assertSame('[2018-01-15, 2018-02-01)', $newSequence->get(0)->format('Y-m-d'));
    }

    public function testMapReturnsSameInstance(): void
    {
        $sequence = new Sequence(
            Period::fromMonth(2018, 1),
            Period::fromDay(2018, 1, 1)
        );

        $newSequence = $sequence->map(function (Period $period): Period {
            return $period;
        });

        self::assertSame($newSequence, $sequence);
    }

    public function testMapperDoesNotReIndexAfterModification(): void
    {
        $sequence = new Sequence(Period::fromDay(2018, 3), Period::fromDay(2018, 1));
        $sequence->sort(function (Period $interval1, Period $interval2): int {
            return $interval1->getStartDate() <=> $interval2->getStartDate();
        });

        $retval = $sequence->map(function (Period $interval): Period {
            return $interval->moveEndDate('+1 DAY');
        });

        self::assertSame(array_keys($sequence->toArray()), array_keys($retval->toArray()));
    }


    public function testArrayAccess(): void
    {
        $sequence = new Sequence();
        $sequence[] = Period::fromMonth(2018, 1);
        self::assertTrue(isset($sequence[0]));
        self::assertEquals(Period::fromMonth(2018, 1), $sequence[0]);
        $sequence[0] = Period::fromMonth(2017, 1);
        self::assertNotEquals(Period::fromMonth(2018, 1), $sequence[0]);
        unset($sequence[0]);
    }

    public function testArrayAccessThrowsTypeError(): void
    {
        self::expectException(TypeError::class);
        $sequence = new Sequence();
        $sequence['foo'] = Period::fromMonth(2017, 1);
    }

    public function testArrayAccessThrowsInvalidIndex(): void
    {
        self::expectException(InvalidIndex::class);
        $sequence = new Sequence();
        $sequence[0] = Period::fromMonth(2017, 1);
    }

    public function testArrayAccessThrowsInvalidIndex2(): void
    {
        self::expectException(InvalidIndex::class);
        $sequence = new Sequence();
        unset($sequence[0]);
    }

    public function testGetTotalTimestampInterval(): void
    {
        self::assertSame((float) 0, (new Sequence())->getTotalTimestampInterval());

        $sequence = new Sequence(Period::fromMonth(2017, 1), Period::fromMonth(2018, 1));
        $period = $sequence->boundaries();
        if (null !== $period) {
            self::assertNotEquals(
                $period->getTimestampInterval(),
                $sequence->getTotalTimestampInterval()
            );
        }
    }
}
