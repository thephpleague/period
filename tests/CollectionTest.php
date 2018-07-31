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
use PHPUnit\Framework\TestCase as TestCase;
use TypeError;
use const ARRAY_FILTER_USE_BOTH;
use function date_create;

abstract class CollectionTest extends TestCase
{
    protected $timezone;

    protected $collection;

    protected $intervals;

    abstract protected function buildCollection(iterable $intervals = []): Collection;

    public function setUp()
    {
        $this->intervals = [
            'first' => Period::createFromDay('2012-02-01'),
            'middle' => Period::createFromMonth('2012-02-01'),
            'last' => Period::createFromWeek('2012-02-01'),
        ];
        $this->timezone = date_default_timezone_get();

        $this->collection = $this->buildCollection($this->intervals);
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testFirst()
    {
        self::assertEquals($this->intervals['first'], $this->collection->first());
        self::assertNull($this->buildCollection()->first());
    }

    public function testLast()
    {
        self::assertEquals($this->intervals['last'], $this->collection->last());
        self::assertNull($this->buildCollection()->last());
    }

    public function testArrayAccess()
    {
        self::assertCount(3, $this->collection);
        self::assertEquals($this->intervals['middle'], $this->collection['middle']);
        self::assertFalse(isset($this->collection['faraway']));
        $this->collection['faraway'] = Period::createFromYear('2013');
        self::assertTrue(isset($this->collection['faraway']));
        $this->collection->push(Period::createFromYear('2013'));
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
        $filter = function ($index, Interval $period) {
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
        $mapper = function ($index, Interval $period) use ($interval) {
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
        $this->collection->map(function ($offset, Interval $period) {
            return true;
        });
    }

    public function testPartition()
    {
        $predicate = function ($index, Interval $period) {
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

    public function testReduce()
    {
        $reducer = function ($carry, $index, Interval $interval) {
            $carry += $interval->getTimestampInterval();

            return $carry;
        };

        $collection = $this->buildCollection([
            'first' => Period::createFromDay('2012-02-01'),
            'middle' => Period::createFromDay('2012-02-02'),
            'last' => Period::createFromDay('2012-02-03'),
        ]);

        $res = $collection->reduce($reducer, 0);
        self::assertSame(86400.0 * 3, $res);
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
    public function testGetGaps(Collection $collection, Collection $expected)
    {
        self::assertEquals($expected->getValues(), $collection->getGaps()->getValues());
    }

    public function providesCollectionForGaps()
    {
        return [
            'no entry' => [
                'collection' => $this->buildCollection(),
                'expected' => $this->buildCollection(),
            ],
            'a single entry' => [
                'collection' => $this->buildCollection([Period::createFromDay('2012-02-01')]),
                'expected' => $this->buildCollection(),
            ],
            'no gaps' => [
                'collection' => $this->buildCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildCollection(),
            ],
            'no gaps from a Period::split(Backwards)' => [
                'collection' => $this->buildCollection(Period::createFromMonth('2012-06-01')->splitBackwards('1 WEEK')),
                'expected' => $this->buildCollection(),
            ],
            'has gaps' => [
                'collection' => $this->buildCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-05-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildCollection([
                    new Period('2012-02-06', '2012-05-01'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForIntersections
     */
    public function testGetIntersections(Collection $collection, Collection $expected)
    {
        self::assertEquals($expected->getValues(), $collection->getIntersections()->getValues());
    }

    public function providesCollectionForIntersections()
    {
        return [
            'no entry' => [
                'collection' => $this->buildCollection(),
                'expected' => $this->buildCollection(),
            ],
            'a single entry' => [
                'collection' => $this->buildCollection([Period::createFromDay('2012-02-01')]),
                'expected' => $this->buildCollection(),
            ],
            'overlaps' => [
                'collection' => $this->buildCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => $this->buildCollection([
                    Period::createFromDay('2012-02-01'),
                    new Period('2012-02-01', '2012-02-06'),
                ]),
            ],
            'should not overlaps' => [
                'collection' => $this->buildCollection(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => $this->buildCollection(),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForPeriods
     */
    public function testGetInterval(Collection $collection, Interval $expected)
    {
        $period = $collection->getInterval();
        self::assertNotNull($period);
        self::assertTrue($expected->equalsTo($period));
    }

    public function providesCollectionForPeriods()
    {
        return [
            'a single entry' => [
                'collection' => $this->buildCollection([Period::createFromDay('2012-02-01')]),
                'expected' => Period::createFromDay('2012-02-01'),
            ],
            'overlaps' => [
                'collection' => $this->buildCollection([
                    'last' => Period::createFromWeek('2012-02-15'),
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                ]),
                'expected' => Period::createFromMonth('2012-02-01'),
            ],
            'should not overlaps' => [
                'collection' => $this->buildCollection(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => Period::createFromMonth('2012-06-01'),
            ],
        ];
    }

    public function testGetIntervalReturnsNullOnEmptyCollection()
    {
        self::assertNull($this->buildCollection()->getInterval());
    }

    public function testPop()
    {
        $collection = $this->buildCollection([Period::createFromDay('2012-02-01')]);
        self::assertCount(1, $collection);
        self::assertInstanceOf(Interval::class, $collection->pop());
        self::assertCount(0, $collection);
        self::assertNull($collection->pop());
    }

    public function testShift()
    {
        $collection = $this->buildCollection([Period::createFromDay('2012-02-01')]);
        self::assertCount(1, $collection);
        self::assertInstanceOf(Interval::class, $collection->shift());
        self::assertCount(0, $collection);
        self::assertNull($collection->shift());
    }

    public function testUnshift()
    {
        $first = Period::createFromDay('2012-02-01');
        $newFirst = Period::createFromDay('2012-02-02');
        $newSecond = Period::createFromDay('2012-02-03');
        $collection = $this->buildCollection([$first]);
        self::assertCount(1, $collection);
        $collection->unshift($newFirst, $newSecond);
        self::assertCount(3, $collection);
        self::assertEquals($collection->last(), $first);
        self::assertEquals($collection->first(), $newFirst);
    }

    public function testIsEmpty()
    {
        self::assertTrue($this->buildCollection()->isEmpty());
        self::assertFalse($this->buildCollection([Period::createFromDay('2012-02-01')])->isEmpty());
    }

    public function testExists()
    {
        $interval = Period::createFromHour('2012-02-01 12:00:00');
        $predicate = function ($offset, Interval $value) use ($interval) {
            return $interval->overlaps($value);
        };

        $collection = $this->buildCollection([
            Period::createFromDay('2012-02-01'),
            Period::createFromDay('2013-02-01'),
            Period::createFromDay('2014-02-01'),
        ]);

        self::assertTrue($collection->some($predicate));
        $collection->clear();
        self::assertFalse($collection->some($predicate));
    }

    public function testEvery()
    {
        $collection = $this->buildCollection([
            Period::createFromDay('2012-02-01'),
            Period::createFromDay('2013-02-01'),
            Period::createFromDay('2014-02-01'),
        ]);
        $interval = $collection->getInterval();
        self::assertInstanceOf(Interval::class, $interval);
        $predicate = function ($offset, Interval $value) use ($interval) {
            return $interval->overlaps($value);
        };

        self::assertTrue($collection->every($predicate));
        $collection->clear();
        self::assertFalse($collection->every($predicate));
    }
}
