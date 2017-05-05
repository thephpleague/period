<?php

namespace League\Period\Test;

use League\Period\Period;
use League\Period\PeriodCollection;
use PHPUnit_Framework_TestCase as TestCase;

class PeriodCollectionTest extends TestCase
{
    public function setUp()
    {

    }


    public function testSort()
    {
        $period1 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T00:00:00Z'), '1 HOUR');
        $period2 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T01:30:00Z'), '30 MINUTES');
        $period3 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T02:45:00Z'), '1 HOUR');

        $periods = [
            0 => $period3,
            1 => $period2,
            2 => $period1
        ];


        $periodCollection = new PeriodCollection($periods);
        $sortedOutput = $periodCollection->sort();
        $this->assertInternalType('array', $sortedOutput);
        $this->assertCount(3, $sortedOutput);

        $this->assertEquals($periods[2], $sortedOutput[0]);
        $this->assertEquals($periods[1], $sortedOutput[1]);
        $this->assertEquals($periods[0], $sortedOutput[2]);

    }


    public function testSortAndConsolidate()
    {
        $period1 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T00:00:00Z'), '1 HOUR');
        $period2 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T01:30:00Z'), '30 MINUTES');
        $period3 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T02:45:00Z'), '1 HOUR');
        $period4 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T02:45:00Z'), '1 HOUR');
        $period5 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T02:45:00Z'), '1 HOUR');

        $periods = [
            0 => $period3,
            1 => $period2,
            2 => $period1,
            3 => $period4,
            4 => $period5
        ];

        $periodCollection = new PeriodCollection($periods);
        $sortedOutput = $periodCollection->sort(true);

        $this->assertInternalType('array', $sortedOutput);
        $this->assertCount(3, $sortedOutput);

        $this->assertEquals($periods[2], $sortedOutput[0]);
        $this->assertEquals($periods[1], $sortedOutput[1]);
        $this->assertEquals($periods[0], $sortedOutput[2]);
    }


    public function testInvert()
    {
        $period1 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T00:00:00Z'), '1 HOUR');
        $period2 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T01:30:00Z'), '30 MINUTES');
        $period3 = Period::createFromDuration(\DateTime::createFromFormat(\DateTime::ISO8601,
            '2016-01-01T02:45:00Z'), '1 HOUR');

        $this->periods = [
            0 => $period3,
            1 => $period2,
            2 => $period1
        ];

        $collection = new PeriodCollection($this->periods);
        $invertedResponse = $collection->invert();
        $this->assertInternalType('array', $invertedResponse);
        $this->assertCount(2, $collection->invert());

        foreach ($invertedResponse as $key => $value) {
            $this->assertInstanceOf(Period::class, $value);
        }

        $this->assertEquals($period1->gap($period2), $invertedResponse[0]);
        $this->assertEquals($period2->gap($period3), $invertedResponse[1]);

    }

}
