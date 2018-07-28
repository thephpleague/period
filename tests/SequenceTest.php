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

namespace LeagueTest\Period;

use DateInterval;
use League\Period\Collection;
use League\Period\Interval;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase as TestCase;
use TypeError;
use const ARRAY_FILTER_USE_BOTH;
use function date_create;

abstract class SequenceTest extends TestCase
{
    protected $timezone;

    protected $collection;

    protected $intervals;

    abstract protected function buildSequence(iterable $intervals = []): Sequence;

    public function setUp()
    {
        $this->intervals = [
            'first' => Period::createFromDay('2012-02-01'),
            'middle' => Period::createFromMonth('2012-02-01'),
            'last' => Period::createFromWeek('2012-02-01'),
        ];
        $this->timezone = date_default_timezone_get();

        $this->collection = $this->buildSequence($this->intervals);
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testFirst()
    {
        self::assertEquals($this->intervals['first'], $this->collection->first());
        self::assertNull($this->buildSequence()->first());
    }

    public function testLast()
    {
        self::assertEquals($this->intervals['last'], $this->collection->last());
        self::assertNull($this->buildSequence()->last());
    }

    public function testArrayAccess()
    {
        self::assertCount(3, $this->collection);
        self::assertEquals($this->intervals['middle'], $this->collection['middle']);
        self::assertFalse(isset($this->collection['faraway']));
        $this->collection['faraway'] = Period::createFromYear('2013');
        self::assertTrue(isset($this->collection['faraway']));
        $this->collection->add(Period::createFromYear('2013'));
        self::assertCount(5, $this->collection);
        unset($this->collection['faraway']);
        self::assertCount(4, $this->collection);
        self::assertFalse(isset($this->collection['faraway']));
    }

    public function testOffsetSetThrowException()
    {
        $this->expectException(TypeError::class);
        $this->collection[] = date_create();
    }

    public function testClear()
    {
        self::assertCount(3, $this->collection);
        $this->collection->clear();
        self::assertCount(0, $this->collection);
    }

    public function testGet()
    {
        self::assertEquals($this->intervals['middle'], $this->collection->get('middle'));
        self::assertNull($this->collection->get('faraway'));
    }

    public function testSet()
    {
        $period = Period::createFromYear('2013');
        self::assertCount(3, $this->collection);
        $this->collection->set('faraway', $period);
        self::assertCount(4, $this->collection);
        self::assertEquals($period, $this->collection->get('faraway'));
        $this->assertSame('faraway', $this->collection->indexOf($period));
    }

    /**
     * @dataProvider invalidSetterArgumentProvider
     */
    public function testSetThrowsException($index, $value)
    {
        $this->expectException(TypeError::class);
        $this->collection->set($index, $value);
    }

    public function invalidSetterArgumentProvider()
    {
        return [
            'invalid value' => [
                'index' => 'foo',
                'value' => 'bart',
            ],
        ];
    }

    public function testRemove()
    {
        $period = $this->collection->last();
        self::assertCount(3, $this->collection);
        self::assertTrue($this->collection->remove($period));
        self::assertCount(2, $this->collection);
        self::assertTrue($this->collection->remove(clone $this->collection['first']));
        self::assertCount(1, $this->collection);
        self::assertFalse($this->collection->remove(Period::createFromYear('1998')));
    }

    public function testRemoveIndex()
    {
        self::assertCount(3, $this->collection);
        $period = $this->collection->removeIndex('last');
        self::assertInstanceOf(Interval::class, $period);
        self::assertCount(2, $this->collection);
        self::assertNull($this->collection->removeIndex('faraway'));
    }

    public function testToArray()
    {
        self::assertSame($this->intervals, $this->collection->toArray());
        self::assertSame(['first', 'middle', 'last'], $this->collection->getKeys());
        self::assertSame(array_values($this->intervals), $this->collection->getValues());
    }

    public function testIterator()
    {
        $iter = 0;
        foreach ($this->collection as $index => $period) {
            self::assertSame($this->intervals[$index], $period);
            ++$iter;
        }
        self::assertCount($iter, $this->intervals);
    }

    public function testHas()
    {
        self::assertTrue($this->collection->has($this->intervals['middle']));
        self::assertTrue($this->collection->has(clone $this->intervals['middle']));
        self::assertFalse($this->collection->has(Period::createFromDay('2008-05-01')));
    }

    public function testHasIndex()
    {
        self::assertTrue($this->collection->hasIndex('middle'));
        self::assertFalse($this->collection->hasIndex('faraway'));
    }

    public function testFilter()
    {
        $filter = function (Interval $period, $index) {
            return $index !== 'middle';
        };

        $newCollection = $this->collection->filter($filter, ARRAY_FILTER_USE_BOTH);
        self::assertInstanceOf(Collection::class, $newCollection);
        self::assertCount(2, $newCollection);
        self::assertFalse(isset($newCollection['middle']));
    }

    public function testMapper()
    {
        $interval = new DateInterval('P2D');
        $mapper = function (Interval $period) use ($interval) {
            return $period
                ->startingOn($period->getStartDate()->sub($interval))
                ->endingOn($period->getStartDate()->add($interval))
            ;
        };

        $newCollection = $this->collection->map($mapper);
        self::assertInstanceOf(Collection::class, $newCollection);
        self::assertCount(3, $newCollection);
        self::assertTrue(isset($newCollection['middle']));
        self::assertNotEquals($newCollection['middle'], $this->collection['middle']);
    }

    public function testMapperThrowsException()
    {
        $this->expectException(TypeError::class);
        $this->collection->map(function (Interval $period) {
            return true;
        });
    }

    public function testPartition()
    {
        $predicate = function (Interval $period, $index) {
            return $index !== 'middle';
        };

        [$matches, $no_matches] = $this->collection->partition($predicate);
        self::assertCount(2, $matches);
        self::assertCount(1, $no_matches);
        self::assertSame($no_matches['middle'], $this->collection['middle']);
    }

    public function testSlice()
    {
        $newCollection = $this->collection->slice(1);
        self::assertCount(2, $newCollection);
        self::assertSame($newCollection['middle'], $this->collection['middle']);
        self::assertFalse(isset($newCollection['first']));
    }

    public function testSort()
    {
        $sort = function (Interval $period1, Interval $period2) {
            return $period2->getEndDate() <=> $period1->getEndDate();
        };

        $this->collection->sort($sort);
        self::assertNotSame($this->collection->first(), $this->collection['first']);
    }

    /**
     * @dataProvider providesCollectionForGaps
     */
    public function testGetGaps(Sequence $collection, Sequence $expected)
    {
        self::assertEquals($expected->getValues(), $collection->getGaps()->getValues());
    }

    public function providesCollectionForGaps()
    {
        return [
            'no entry' => [
                'collection' => $this->buildSequence(),
                'expected' => $this->buildSequence(),
            ],
            'a single entry' => [
                'collection' => $this->buildSequence([Period::createFromDay('2012-02-01')]),
                'expected' => $this->buildSequence(),
            ],
            'no gaps' => [
                'collection' => $this->buildSequence([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildSequence(),
            ],
            'no gaps from a Period::split(Backwards)' => [
                'collection' => $this->buildSequence(Period::createFromMonth('2012-06-01')->splitBackwards('1 WEEK')),
                'expected' => $this->buildSequence(),
            ],
            'has gaps' => [
                'collection' => $this->buildSequence([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-05-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildSequence([
                    new Period('2012-02-06', '2012-05-01'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForIntersections
     */
    public function testGetIntersections(Sequence $collection, Sequence $expected)
    {
        self::assertEquals($expected->getValues(), $collection->getIntersections()->getValues());
    }

    public function providesCollectionForIntersections()
    {
        return [
            'no entry' => [
                'collection' => $this->buildSequence(),
                'expected' => $this->buildSequence(),
            ],
            'a single entry' => [
                'collection' => $this->buildSequence([Period::createFromDay('2012-02-01')]),
                'expected' => $this->buildSequence(),
            ],
            'overlaps' => [
                'collection' => $this->buildSequence([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildSequence([
                    Period::createFromDay('2012-02-01'),
                    new Period('2012-02-01', '2012-02-06'),
                ]),
            ],
            'should not overlaps' => [
                'collection' => $this->buildSequence(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => $this->buildSequence(),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForPeriods
     */
    public function testgetInterval(Sequence $collection, Interval $expected)
    {
        $period = $collection->getInterval();
        self::assertNotNull($period);
        self::assertTrue($expected->equalsTo($period));
    }

    public function providesCollectionForPeriods()
    {
        return [
            'a single entry' => [
                'collection' => $this->buildSequence([Period::createFromDay('2012-02-01')]),
                'expected' => Period::createFromDay('2012-02-01'),
            ],
            'overlaps' => [
                'collection' => $this->buildSequence([
                    'last' => Period::createFromWeek('2012-02-15'),
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                ]),
                'expected' => Period::createFromMonth('2012-02-01'),
            ],
            'should not overlaps' => [
                'collection' => $this->buildSequence(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => Period::createFromMonth('2012-06-01'),
            ],
        ];
    }

    public function testgetIntervalReturnsNullOnEmptyCollection()
    {
        self::assertNull($this->buildSequence()->getInterval());
    }
}
