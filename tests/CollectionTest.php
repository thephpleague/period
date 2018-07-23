<?php

/**
 * League.Uri (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period\Test;

use DateInterval;
use League\Period\Collection;
use League\Period\Period;
use League\Period\PeriodInterface;
use PHPUnit\Framework\TestCase as TestCase;
use TypeError;
use const ARRAY_FILTER_USE_BOTH;
use function date_create;

class CollectionTest extends TestCase
{
    protected $timezone;

    protected $collection;

    protected $elements;

    public function setUp()
    {
        $this->elements = [
            'first' => Period::createFromDay('2012-02-01'),
            'middle' => Period::createFromMonth('2012-02-01'),
            'last' => Period::createFromWeek('2012-02-01'),
        ];
        $this->timezone = date_default_timezone_get();
        $this->collection = new Collection($this->elements);
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testFirst()
    {
        self::assertEquals($this->elements['first'], $this->collection->first());
        self::assertNull((new Collection())->first());
    }

    public function testLast()
    {
        self::assertEquals($this->elements['last'], $this->collection->last());
        self::assertNull((new Collection())->last());
    }

    public function testArrayAccess()
    {
        self::assertCount(3, $this->collection);
        self::assertEquals($this->elements['middle'], $this->collection['middle']);
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
        self::assertEquals($this->elements['middle'], $this->collection->get('middle'));
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
            'null index' => [
                'index' => null,
                'value' => Period::createFromYear('2013'),
            ],
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
        self::assertInstanceOf(PeriodInterface::class, $period);
        self::assertCount(2, $this->collection);
        self::assertNull($this->collection->removeIndex('faraway'));
    }

    public function testToArray()
    {
        self::assertSame($this->elements, $this->collection->toArray());
        self::assertSame(['first', 'middle', 'last'], $this->collection->getKeys());
        self::assertSame(array_values($this->elements), $this->collection->getValues());
    }

    public function testIterator()
    {
        $iter = 0;
        foreach ($this->collection as $index => $period) {
            self::assertSame($this->elements[$index], $period);
            ++$iter;
        }
        self::assertCount($iter, $this->elements);
    }

    public function testHas()
    {
        self::assertTrue($this->collection->contains($this->elements['middle']));
        self::assertTrue($this->collection->contains(clone $this->elements['middle']));
        self::assertFalse($this->collection->contains(Period::createFromDay('2008-05-01')));
    }

    public function testHasKey()
    {
        self::assertTrue($this->collection->containsKey('middle'));
        self::assertFalse($this->collection->containsKey('faraway'));
    }

    public function testFilter()
    {
        $filter = function (PeriodInterface $period, $index) {
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
        $mapper = function (PeriodInterface $period) use ($interval) {
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
        $this->collection->map(function (PeriodInterface $period) {
            return true;
        });
    }

    public function testPartition()
    {
        $predicate = function (PeriodInterface $period, $index) {
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
        $sort = function (PeriodInterface $period1, PeriodInterface $period2) {
            return $period2->getEndDate() <=> $period1->getEndDate();
        };

        $this->collection->sort($sort);
        self::assertNotSame($this->collection->first(), $this->collection['first']);
    }

    /**
     * @dataProvider providesCollectionForGaps
     *
     */
    public function testGetGaps(Collection $collection, Collection $expected)
    {
        self::assertEquals($expected, $collection->getGaps());
    }

    public function providesCollectionForGaps()
    {
        return [
            'no entry' => [
                'collection' => new Collection(),
                'expected' => new Collection(),
            ],
            'a single entry' => [
                'collection' => new Collection([Period::createFromDay('2012-02-01')]),
                'expected' => new Collection(),
            ],
            'no gaps' => [
                'collection' => new Collection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new Collection(),
            ],
            'no gaps from a Period::split(Backwards)' => [
                'collection' => new Collection(Period::createFromMonth('2012-06-01')->splitBackwards('1 WEEK')),
                'expected' => new Collection(),
            ],
            'has gaps' => [
                'collection' => new Collection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-05-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new Collection([
                    new Period('2012-02-06', '2012-05-01'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForIntersections
     *
     */
    public function testGetIntersections(Collection $collection, Collection $expected)
    {
        self::assertEquals($expected, $collection->getIntersections());
    }

    public function providesCollectionForIntersections()
    {
        return [
            'no entry' => [
                'collection' => new Collection(),
                'expected' => new Collection(),
            ],
            'a single entry' => [
                'collection' => new Collection([Period::createFromDay('2012-02-01')]),
                'expected' => new Collection(),
            ],
            'overlaps' => [
                'collection' => new Collection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new Collection([
                    Period::createFromDay('2012-02-01'),
                    new Period('2012-02-01', '2012-02-06'),
                ]),
            ],
            'should not overlaps' => [
                'collection' => new Collection(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => new Collection(),
            ],
        ];
    }
}
