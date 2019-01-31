<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use DateInterval;
use League\Period\Duration;
use League\Period\Period;

class DurationTest extends TestCase
{
    public function testCreateFromDateString(): void
    {
        $duration = Duration::createFromDateString('+1 DAY');
        self::assertSame(1, $duration->d);
        self::assertFalse($duration->days);
        $altduration = Duration::createFromDateString('foobar');
        self::assertSame(0, $altduration->s);
    }

    /**
     * @dataProvider getISO8601StringProvider
     *
     * @param mixed $input duration
     */
    public function testISO8601String($input, string $expected): void
    {
        self::assertSame($expected, (string) Duration::create($input));
    }

    public function getISO8601StringProvider(): array
    {
        return [
            [
                'input' => new DateInterval('P1M'),
                'expected' => 'P1M',
            ],
            [
                'input' => new DateInterval('PT1H'),
                'expected' => 'PT1H',
            ],
            [
                'input' => Period::fromMonth(2018, 2),
                'expected' => 'P1M',
            ],
            [
                'input' => '1 WEEK',
                'expected' => 'P7D',
            ],
            [
                'input' => 0,
                'expected' => 'PT0S',
            ],
            [
                'input' => 3,
                'expected' => 'PT3S',
            ],
            [
                'input' => new Period('2012-02-06 08:25:32.000120', '2012-02-06 08:25:32.000130'),
                'expected' => 'PT0.00001S',
            ],
            [
                'input' => new Duration('PT0.0001S'),
                'expected' => 'PT0.0001S',
            ],
       ];
    }

    public function testIntervalWithFraction(): void
    {
        $duration = new Duration('PT3.1S');
        self::assertSame('PT3.1S', (string) $duration);

        $duration = new Duration('P0000-00-00T00:05:00.023658');
        self::assertSame('PT5M0.023658S', (string) $duration);
        self::assertSame(0.023658, $duration->f);
    }

    /**
     * @dataProvider fromChronoProvider
     */
    public function testCreateFromTimeString(string $chronometer, string $expected, int $revert): void
    {
        $duration = Duration::create($chronometer);
        self::assertSame($expected, (string) $duration);
        self::assertSame($revert, $duration->invert);
    }

    public function fromChronoProvider(): iterable
    {
        return [
            [
                'chronometer' => '1',
                'expected' => 'PT1S',
                'invert' => 0,
            ],
            [
                'chronometer' => '1:2',
                'expected' => 'PT1M2S',
                'invert' => 0,
            ],
            [
                'chronometer' => '1:2:3',
                'expected' => 'PT1H2M3S',
                'invert' => 0,
            ],
            [
                'chronometer' => '00001',
                'expected' => 'PT1S',
                'invert' => 0,
            ],
            [
                'chronometer' => '00001:00002:000003.0004',
                'expected' => 'PT1H2M3.0004S',
                'invert' => 0,
            ],
            [
                'chronometer' => '-12:28.5',
                'expected' => 'PT12M28.5S',
                'invert' => 1,
            ],
        ];
    }

    /**
     * @dataProvider withoutCarryOverDataProvider
     */
    public function testWithoutCarryOver(string $input, string $expected): void
    {
        $duration = new Duration($input);
        self::assertSame($expected, (string) $duration->withoutCarryOver());
    }

    public function withoutCarryOverDataProvider(): iterable
    {
        return [
            [
                'input' => 'PT3H',
                'expected' => 'PT3H',
            ],
            [
                'input' => 'PT24H',
                'expected' => 'P1D',
            ],
            [
                'input' => 'P31D',
                'expected' => 'P1M',
            ],
            [
                'input' => 'P12M',
                'expected' => 'P1Y',
            ],
        ];
    }
}
