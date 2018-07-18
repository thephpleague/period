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
use League\Period\Exception;
use League\Period\Period;
use League\Period\PeriodCollection;
use League\Period\PeriodInterface;
use PHPUnit\Framework\TestCase as TestCase;
use const ARRAY_FILTER_USE_BOTH;
use function date_create;

class PeriodCollectionTest extends TestCase
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
        $this->collection = new PeriodCollection($this->elements);
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testFirst()
    {
        self::assertEquals($this->elements['first'], $this->collection->first());
    }

    public function testLast()
    {
        self::assertEquals($this->elements['last'], $this->collection->last());
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
        $this->expectException(Exception::class);
        $this->collection[] = date_create();
    }

    public function testGetPeriod()
    {
        self::assertInstanceOf(Period::class, $this->collection->getPeriod());
        self::assertNull((new PeriodCollection())->getPeriod());
    }

    public function testClear()
    {
        self::assertCount(3, $this->collection);
        $this->collection->clear();
        self::assertCount(0, $this->collection);
    }

    public function testToString()
    {
        self::assertSame('', (string) new PeriodCollection());
        self::assertSame((string) $this->collection, (string) $this->collection->getPeriod());
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
        self::assertTrue($this->collection->has($this->elements['middle']));
        self::assertFalse($this->collection->has(clone $this->elements['middle']));
    }

    public function testRemove()
    {
        self::assertTrue($this->collection->remove($this->collection['middle']));
        self::assertFalse($this->collection->remove(clone$this->collection['first']));
    }

    public function testFilter()
    {
        $filter = function (PeriodInterface $period, $index) {
            return $index !== 'middle';
        };

        $newCollection = $this->collection->filter($filter, ARRAY_FILTER_USE_BOTH);
        self::assertInstanceOf(PeriodCollection::class, $newCollection);
        self::assertCount(2, $newCollection);
        self::assertFalse(isset($newCollection['middle']));
    }

    public function testMapper()
    {
        $mapper = function (PeriodInterface $period) {
            return $period->expand(new DateInterval('P2D'));
        };

        $newCollection = $this->collection->map($mapper);
        self::assertInstanceOf(PeriodCollection::class, $newCollection);
        self::assertCount(3, $newCollection);
        self::assertTrue(isset($newCollection['middle']));
        self::assertNotEquals($newCollection['middle'], $this->collection['middle']);
    }

    public function testMapperThrowsException()
    {
        $this->expectException(Exception::class);
        $this->collection->map(function (PeriodInterface $period) {
            return true;
        });
    }

    public function testSplit()
    {
        $splitter = function (PeriodInterface $period, $index) {
            return $index !== 'middle';
        };

        [$matches, $no_matches] = $this->collection->split($splitter);
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
    public function testInnerGaps(PeriodCollection $collection, PeriodCollection $expected)
    {
        self::assertEquals($expected, $collection->gaps());
    }

    public function providesCollectionForGaps()
    {
        return [
            'no entry' => [
                'collection' => new PeriodCollection(),
                'expected' => new PeriodCollection(),
            ],
            'a single entry' => [
                'collection' => new PeriodCollection([Period::createFromDay('2012-02-01')]),
                'expected' => new PeriodCollection(),
            ],
            'no gaps' => [
                'collection' => new PeriodCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new PeriodCollection(),
            ],
            'no gaps from a Period::split(Backwards)' => [
                'collection' => new PeriodCollection(Period::createFromMonth('2012-06-01')->splitBackwards('1 WEEK')),
                'expected' => new PeriodCollection(),
            ],
            'has gaps' => [
                'collection' => new PeriodCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-05-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new PeriodCollection([
                    new Period('2012-02-06', '2012-05-01'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providesCollectionForOverlaps
     *
     */
    public function testInnerOverlaps(PeriodCollection $collection, PeriodCollection $expected)
    {
        self::assertEquals($expected, $collection->overlaps());
    }

    public function providesCollectionForOverlaps()
    {
        return [
            'no entry' => [
                'collection' => new PeriodCollection(),
                'expected' => new PeriodCollection(),
            ],
            'a single entry' => [
                'collection' => new PeriodCollection([Period::createFromDay('2012-02-01')]),
                'expected' => new PeriodCollection(),
            ],
            'overlaps' => [
                'collection' => new PeriodCollection([
                    'first' => Period::createFromDay('2012-02-01'),
                    'middle' => Period::createFromMonth('2012-02-01'),
                    'last' => Period::createFromWeek('2012-02-01'),
                ]),
                'expected' => new PeriodCollection([
                    Period::createFromDay('2012-02-01'),
                    new Period('2012-02-01', '2012-02-06'),
                ]),
            ],
            'should not overlaps' => [
                'collection' => new PeriodCollection(Period::createFromMonth('2012-06-01')->split('1 WEEK')),
                'expected' => new PeriodCollection(),
            ],
        ];
    }
}
