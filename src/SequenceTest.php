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

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateInterval;
use DateTime;
use DateTimeImmutable;

use function json_encode;

final class SequenceTest extends PeriodTestCase
{
    public function testIsEmpty(): void
    {
        $sequence = new Sequence();
        self::assertTrue($sequence->isEmpty());
        self::assertCount(0, $sequence);
        self::assertNull($sequence->length());
        $sequence->push(Period::fromDay(2012, 6, 23));
        self::assertFalse($sequence->isEmpty());
        self::assertCount(1, $sequence);
        self::assertInstanceOf(Period::class, $sequence->length());
    }

    public function testConstructor(): void
    {
        $sequence = new Sequence(Period::fromDay(2012, 6, 23), Period::fromDay(2012, 6, 23));
        self::assertCount(2, $sequence);
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
        $this->expectException(InaccessibleInterval::class);
        $sequence->remove(1);
    }

    public function testGetter(): void
    {
        $event1 = Period::fromDay(2012, 6, 23);
        $event2 = Period::fromDay(2012, 6, 23);
        $event3 = Period::fromDay(2012, 6, 25);
        $sequence = new Sequence($event1, $event2);
        self::assertInstanceOf(Period::class, $sequence->length());
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
        self::assertNull($sequence->length());
    }

    public function testGetThrowsExceptionWithInvalidPositiveIndex(): void
    {
        $this->expectException(InaccessibleInterval::class);
        (new Sequence(DatePoint::fromDateString('2011-06-23')->day()))->get(3);
    }


    public function testGetThrowsExceptionWithInvalidNegativeIndex(): void
    {
        $this->expectException(InaccessibleInterval::class);
        (new Sequence(DatePoint::fromDateString('2011-06-23')->day()))->get(-3);
    }

    public function testNegativeOffsetWithASequenceWithASingleItem(): void
    {
        $sequence = new Sequence(DatePoint::fromDateString('today')->day());
        self::assertSame($sequence[-1], $sequence[0]);
    }

    public function testSetter(): void
    {
        $sequence = new Sequence(DatePoint::fromDateString('2011-06-23')->day(), DatePoint::fromDateString('2011-06-23')->day());
        $sequence->set(0, DatePoint::fromDateString('2011-06-23')->day());
        self::assertEquals(DatePoint::fromDateString('2011-06-23')->day(), $sequence->get(0));
        $sequence->set(1, Period::fromDay(2012, 6, 23));
        $sequence->set(0, DatePoint::fromDateString('2013-06-23')->day());
        self::assertEquals(Period::fromDay(2012, 6, 23), $sequence->get(1));
        self::assertEquals(DatePoint::fromDateString('2013-06-23')->day(), $sequence->get(0));
        $this->expectException(InaccessibleInterval::class);
        $sequence->set(3, DatePoint::fromDateString('2013-06-23')->day());
    }

    public function testInsert(): void
    {
        $sequence = new Sequence();
        $sequence->insert(0, DatePoint::fromDateString('2010-06-23')->day());
        self::assertCount(1, $sequence);
        $sequence->insert(1, DatePoint::fromDateString('2011-06-24')->day());
        self::assertCount(2, $sequence);
        $sequence->insert(-1, DatePoint::fromDateString('2012-06-25')->day());
        self::assertCount(3, $sequence);
        self::assertTrue(DatePoint::fromDateString('2012-06-25')->day()->equals($sequence->get(1)));
        $this->expectException(InaccessibleInterval::class);
        $sequence->insert(42, DatePoint::fromDateString('2011-06-23')->day());
    }

    public function testJsonSerialize(): void
    {
        $day = DatePoint::fromDateString('2010-06-23')->day();
        self::assertSame('['.json_encode($day).']', json_encode(new Sequence($day)));
    }

    public function testFilterReturnsNewInstance(): void
    {
        $sequence = new Sequence(Period::fromDay(2012, 6, 23), DatePoint::fromDateString('2012-06-12')->day());
        $newCollection = $sequence->filter(fn (Period $period): bool => $period->startDate == new DateTimeImmutable('2012-06-23'));

        self::assertNotEquals($newCollection, $sequence);
        self::assertCount(1, $newCollection);
    }


    public function testFilterReturnsSameInstance(): void
    {
        $sequence = new Sequence(
            Period::fromDay(2012, 6, 23),
            DatePoint::fromDateString('2012-06-12')->day()
        );

        self::assertSame($sequence, $sequence->filter(fn (Period $interval): bool => $interval->endDate >= $interval->startDate));
    }

    public function testSortedReturnsSameInstance(): void
    {
        $sequence = new Sequence(
            Period::fromDay(2012, 6, 23),
            DatePoint::fromDateString('2012-06-12')->day()
        );

        self::assertSame($sequence, $sequence->sorted(fn (Period $event1, Period $event2): int => strlen(get_class($event1)) - strlen(get_class($event2))));
    }

    public function testSortedReturnsNewInstance(): void
    {
        $sequence = new Sequence(
            Period::fromMonth(2012, 6),
            Period::fromDay(2012, 6, 23),
            Period::fromIsoWeek(2018, 3)
        );

        self::assertNotSame($sequence, $sequence->sorted(fn (Period $event1, Period $event2): int => $event1->durationCompare($event2)));
    }

    public function testSort(): void
    {
        $day1 = Period::fromDay(2012, 6, 23);
        $day2 = DatePoint::fromDateString('2012-06-12')->day();
        $sequence = new Sequence($day1, $day2);
        self::assertSame([0 => $day1, 1 => $day2], $sequence->toList());

        $sequence->sort(fn (Period $period1, Period $period2): int => $period1->startDate <=> $period2->startDate);
        self::assertSame([0 => $day2, 1 => $day1], $sequence->toList());
    }

    public function testSome(): void
    {
        $interval = Period::after(new DateTimeImmutable('2012-02-01 12:00:00'), Duration::fromDateString('1 HOUR'));
        $predicate = fn (Period $event): bool => $interval->overlaps($event);

        $sequence = new Sequence(
            DatePoint::fromDateString('2012-02-01')->day(),
            DatePoint::fromDateString('2013-02-01')->day(),
            DatePoint::fromDateString('2014-02-01')->day()
        );

        self::assertTrue($sequence->some($predicate));
        self::assertFalse((new Sequence())->some($predicate));
    }

    public function testEvery(): void
    {
        $sequence = new Sequence(
            DatePoint::fromDateString('2012-02-01')->day(),
            DatePoint::fromDateString('2013-02-01')->day(),
            DatePoint::fromDateString('2014-02-01')->day()
        );

        $interval = Period::after(new DateTime('2012-01-01'), Duration::fromDateString('5 YEARS'));
        $predicate = fn (Period $event): bool => $interval->contains($event);

        self::assertTrue($sequence->every($predicate));
        self::assertFalse((new Sequence())->every($predicate));
        self::assertFalse((new Sequence(DatePoint::fromDateString('1988-02-01')->day()))->every($predicate));
    }

    /**
     * subtract test 1.
     *
     *  [-------------)      [------------)
     *                   -
     *       [--)         [---------------------)
     *                   =
     *  [----)   [----)
     */
    public function testSubtract1(): void
    {
        $sequenceA = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-10')),
            Period::fromDate(new DateTime('2000-01-12'), new DateTime('2000-01-20'))
        );
        $sequenceB = new Sequence(
            Period::fromDate(new DateTime('2000-01-05'), new DateTime('2000-01-08')),
            Period::fromDate(new DateTime('2000-01-11'), new DateTime('2000-01-25'))
        );
        $diff = $sequenceA->subtract($sequenceB);

        self::assertCount(2, $diff);
        self::assertSame('[2000-01-01, 2000-01-05)', $diff->get(0)->toIso80000('Y-m-d'));
        self::assertSame('[2000-01-08, 2000-01-10)', $diff->get(1)->toIso80000('Y-m-d'));
        self::assertEquals($diff, $sequenceA->subtract($sequenceB));
    }

    /**
     * subtract test 2.
     *
     *  [------)      [------)      [------)
     *                   -
     *  [------------------------------------------)
     *                   =
     *  ()
     */
    public function testSubtract2(): void
    {
        $sequenceA = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-05')),
            Period::fromDate(new DateTime('2000-01-10'), new DateTime('2000-01-15')),
            Period::fromDate(new DateTime('2000-01-20'), new DateTime('2000-01-25'))
        );
        $sequenceB = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-30'))
        );
        $diff = $sequenceA->subtract($sequenceB);

        self::assertCount(0, $diff);
    }

    /**
     * subtract test 3.
     */
    public function testSubtract3(): void
    {
        $sequenceA = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-10')),
            Period::fromDate(new DateTime('2000-01-12'), new DateTime('2000-01-20'))
        );
        $sequenceB = new Sequence();

        $diff1 = $sequenceA->subtract($sequenceB);
        self::assertCount(2, $diff1);
        self::assertSame('[2000-01-01, 2000-01-10)', $diff1->get(0)->toIso80000('Y-m-d'));
        self::assertSame('[2000-01-12, 2000-01-20)', $diff1->get(1)->toIso80000('Y-m-d'));

        $diff2 = $sequenceB->subtract($sequenceA);
        self::assertCount(0, $diff2);
    }

    /**
     * subtract test 4.
     */
    public function testSubtract4(): void
    {
        $sequenceA = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-10')),
            Period::fromDate(new DateTime('2000-01-12'), new DateTime('2000-01-20'))
        );
        $sequenceB = new Sequence(Period::fromDate(new DateTime('2003-01-12'), new DateTime('2003-01-20')));
        self::assertSame($sequenceA, $sequenceA->subtract($sequenceB));
    }

    /**
     * subtract test 5.
     */
    public function testSubtract5(): void
    {
        $sequenceA = new Sequence(
            Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-10')),
            Period::fromDate(new DateTime('2001-01-01'), new DateTime('2001-01-10'))
        );
        $sequenceB = new Sequence(Period::fromDate(new DateTime('2000-01-01'), new DateTime('2000-01-10')));
        self::assertCount(0, $sequenceB->subtract($sequenceA));
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
            Period::fromDate(new DateTime('2018-01-01'), new DateTime('2018-01-31')),
            Period::fromDate(new DateTime('2018-01-10'), new DateTime('2018-01-15')),
            Period::fromDate(new DateTime('2018-01-10'), new DateTime('2018-01-31'))
        );
        $intersections = $sequence->intersections();

        self::assertCount(2, $intersections);
        self::assertSame('[2018-01-10, 2018-01-15)', $intersections->get(0)->toIso80000('Y-m-d'));
        self::assertSame('[2018-01-10, 2018-01-31)', $intersections->get(1)->toIso80000('Y-m-d'));
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
            Period::fromDate(new DateTime('2018-01-01'), new DateTime('2018-01-31')),
            Period::fromDate(new DateTime('2018-02-10'), new DateTime('2018-02-20')),
            Period::fromDate(new DateTime('2018-03-01'), new DateTime('2018-03-31')),
            Period::fromDate(new DateTime('2018-01-20'), new DateTime('2018-03-10'))
        );
        $intersections = $sequence->intersections();
        self::assertCount(3, $intersections);
        self::assertSame('[2018-01-20, 2018-01-31)', $intersections->get(0)->toIso80000('Y-m-d'));
        self::assertSame('[2018-02-10, 2018-02-20)', $intersections->get(1)->toIso80000('Y-m-d'));
        self::assertSame('[2018-03-01, 2018-03-10)', $intersections->get(2)->toIso80000('Y-m-d'));
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
            DatePoint::fromDateString('2018-11-29')->day(),
            Period::after(new DateTimeImmutable('2018-11-29 + 7 DAYS'), DateInterval::createFromDateString('1 DAY')),
            Period::around(new DateTimeImmutable('2018-11-29'), DateInterval::createFromDateString('4 DAYS'))
        );

        $gaps = $sequence->gaps();
        self::assertCount(1, $gaps);
        self::assertSame('[2018-12-03, 2018-12-06)', $gaps->get(0)->toIso80000('Y-m-d'));
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
            DatePoint::fromDateString('2018-11-29')->day(),
            Period::around(new DateTime('2018-11-29'), Duration::fromDateString('4 DAYS'))
        );

        $gaps = $sequence->gaps();
        self::assertTrue($gaps->isEmpty());
    }

    public function testUnionReturnsSameInstance(): void
    {
        $sequence = new Sequence(DatePoint::fromDateString('2018-11-29')->day());
        self::assertSame($sequence, $sequence->unions());
    }

    public function testUnion(): void
    {
        $sequence = new Sequence(
            DatePoint::fromDateString('2018-11-29')->year(),
            DatePoint::fromDateString('2018-11-29')->month(),
            Period::around(new DateTimeImmutable('2016-06-01'), Duration::fromDateString('3 MONTHS'))
        );

        $unions = $sequence->unions();
        self::assertEquals($sequence->length(), $unions->length());
        self::assertTrue($unions->intersections()->isEmpty());
        self::assertEquals($sequence->gaps(), $unions->gaps());
        self::assertTrue(Period::around(new DateTimeImmutable('2016-06-01'), Duration::fromDateString('3 MONTHS'))->equals($unions->get(0)));
        self::assertTrue(DatePoint::fromDateString('2018-11-29')->year()->equals($unions->get(1)));
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

            return $period->startingOn(new DateTimeImmutable('2018-01-15'));
        });

        self::assertSame($newSequence->get(1), $sequence->get(1));
        self::assertSame('[2018-01-15, 2018-02-01)', $newSequence->get(0)->toIso80000('Y-m-d'));
    }

    public function testMapReturnsSameInstance(): void
    {
        $sequence = new Sequence(
            Period::fromMonth(2018, 1),
            Period::fromDay(2018, 1, 1)
        );

        $newSequence = $sequence->map(fn (Period $period): Period => $period);

        self::assertSame($newSequence, $sequence);
    }

    public function testMapperDoesNotReIndexAfterModification(): void
    {
        $sequence = new Sequence(Period::fromDay(2018, 3, 1), Period::fromDay(2018, 1, 1));
        $sequence->sort(fn (Period $interval1, Period $interval2): int => $interval1->startDate <=> $interval2->startDate);

        $retval = $sequence->map(fn (Period $interval): Period => $interval->moveEndDate(Duration::fromDateString('+1 DAY')));

        self::assertSame(array_keys($sequence->toList()), array_keys($retval->toList()));
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

    public function testArrayAccessThrowsInvalidIndex(): void
    {
        $this->expectException(InaccessibleInterval::class);
        $sequence = new Sequence();
        $sequence[0] = Period::fromMonth(2017, 1);
    }

    public function testArrayAccessThrowsInvalidIndex2(): void
    {
        $this->expectException(InaccessibleInterval::class);
        $sequence = new Sequence();
        unset($sequence[0]);
    }

    public function testTotalTimeDuration(): void
    {
        self::assertSame(0, (new Sequence())->totalTimeDuration());

        $sequence = new Sequence(Period::fromMonth(2017, 1), Period::fromMonth(2018, 1));
        $period = $sequence->length();
        if (null !== $period) {
            self::assertNotEquals($period->timeDuration(), $sequence->totalTimeDuration());
        }
    }

    public function testIssue134RemoveDuplicateOnIntersection(): void
    {
        $p1 = Period::fromDate('2023-01-01 00:00:00', '2023-01-03 00:00:00');
        $p2 = Period::fromDate('2023-01-01 00:00:00', '2023-01-03 00:00:00');
        $p3 = Period::fromDate('2023-01-01 00:00:00', '2023-01-03 00:00:00');
        $p4 = Period::fromDate('2023-01-02 00:00:00', '2023-01-04 00:00:00');
        $p5 = Period::fromDate('2023-01-02 00:00:00', '2023-01-04 00:00:00');

        $sequence = new Sequence($p1, $p2, $p3, $p4, $p5);
        self::assertCount(2, $sequence->intersections());
    }
}
