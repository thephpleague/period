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
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use TypeError;
use function League\Period\datepoint;
use function League\Period\duration;

class FunctionTest extends TestCase
{
    /**
     * @dataProvider datepointProvider
     */
    public function testDatepoint(DateTimeImmutable $expected, $input)
    {
        $datepoint = datepoint($input);
        self::assertInstanceOf(DateTimeImmutable::class, $datepoint);
        self::assertEquals($expected, $datepoint);
    }

    public function datepointProvider(): array
    {
        $date = '2012-01-05';
        $expected = new DateTimeImmutable($date);
        return [
            'string' => [
                'expected' => $expected,
                'input' => $date,
            ],
            'DateTime' => [
                'expected' => $expected,
                'input' => new DateTime($date),
            ],
            'DateTimeImmutable' => [
                'expected' => $expected,
                'input' => $expected,
            ],
            'int' => [
                'expected' => $expected,
                'input' => $expected->getTimestamp(),
            ],
        ];
    }

    public function testDatepointThrowsTypeError()
    {
        self::expectException(TypeError::class);
        datepoint([]);
    }

    /**
     * @dataProvider durationProvider
     */
    public function testDuration(DateInterval $expected, $input)
    {
        $duration = duration($input);
        self::assertInstanceOf(DateInterval::class, $duration);
        self::assertEquals($expected, $duration);
    }

    public function durationProvider(): array
    {
        return [
            'DateInterval' => [
                'expected' => new DateInterval('P1D'),
                'input' => new DateInterval('P1D'),
            ],
            'string' => [
                'expected' => new DateInterval('P1D'),
                'input' => '+1 DAY',
            ],
            'int' => [
                'expected' => new DateInterval('PT30S'),
                'input' => 30,
            ],
        ];
    }

    public function testDurationThrowsTypeError()
    {
        self::expectException(TypeError::class);
        duration([]);
    }
}
