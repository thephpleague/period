<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateInterval;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use function version_compare;
use const PHP_VERSION;

final class DurationTest extends TestCase
{
    /** @var string **/
    private $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    private function formatDuration(Duration $duration): string
    {
        $interval = $duration->toDateInterval();

        $date = 'P';
        foreach (['Y' => 'y', 'M' => 'm', 'D' => 'd'] as $key => $value) {
            if (0 !== $interval->$value) {
                $date .= '%'.$value.$key;
            }
        }

        $time = 'T';
        foreach (['H' => 'h', 'M' => 'i'] as $key => $value) {
            if (0 !== $interval->$value) {
                $time .= '%'.$value.$key;
            }
        }

        if (0.0 !== $interval->f) {
            $second = $interval->s + $interval->f;
            if (0 > $interval->s) {
                $second = $interval->s - $interval->f;
            }

            $second = rtrim(sprintf('%f', $second), '0');

            return $interval->format($date.$time).$second.'S';
        }

        if (0 !== $interval->s) {
            return $interval->format($date.$time.'%sS');
        }

        if ('T' !== $time) {
            return $interval->format($date.$time);
        }

        if ('P' !== $date) {
            return $interval->format($date);
        }

        return 'PT0S';
    }

    public function testCreateFromDateString(): void
    {
        $duration = Duration::fromDateString('+1 DAY');
        if (false !== $duration) {
            self::assertSame(1, $duration->toDateInterval()->d);
            self::assertFalse($duration->toDateInterval()->days);
        }

        if (version_compare(PHP_VERSION, '7.2.17', '<')
            && version_compare(PHP_VERSION, '7.3.4', '<')) {
            /** @var Duration $altduration */
            $altduration = Duration::fromDateString('foobar');
            self::assertSame(0, $altduration->toDateInterval()->s);
        }
    }

    /**
     * @runInSeparateProcess
     * @dataProvider getDurationCreateSuccessfulProvider
     *
     * @param mixed $input duration
     */
    public function testDurationCreateNamedConstructor($input, string $expected): void
    {
        self::assertSame($expected, $this->formatDuration(Duration::create($input)));
    }

    public function getDurationCreateSuccessfulProvider(): array
    {
        return [
            'date only' => [
                'input' => new DateInterval('P1M'),
                'expected' => 'P1M',
            ],
            'time only' => [
                'input' => new DateInterval('PT1H'),
                'expected' => 'PT1H',
            ],
            'from a period object' => [
                'input' => Period::fromMonth(2018, 2),
                'expected' => 'P1M',
            ],
            'from a spec string' => [
                'input' => 'PT1H',
                'expect' => 'PT1H',
            ],
            'from a week' => [
                'input' => '1 WEEK',
                'expected' => 'P7D',
            ],
            'from an integer' => [
                'input' => 0,
                'expected' => 'PT0S',
            ],
            'microseconds' => [
                'input' => Period::fromDatepoint('2012-02-06 08:25:32.000120', '2012-02-06 08:25:32.000130'),
                'expected' => 'PT0.00001S',
            ],
            'negative seconds' => [
                'input' => '-3 seconds 10 microseconds',
                'expected' => 'PT-3.00001S',
            ],
            'duration with microseconds' => [
                'input' => Duration::fromIsoString('PT0.0001S'),
                'expected' => 'PT0.0001S',
            ],
       ];
    }

    public function getDurationCreateFailsProvider(): iterable
    {
        return [
            'invalid interval spec 1' => ['PT'],
            'invalid interval spec 2' => ['P'],
            'invalid interval spec 3' => ['PT1'],
            'invalid interval spec 4' => ['P3'],
            'invalid interval spec 5' => ['PT3X'],
            'invalid interval spec 6' => ['PT3s'],
            'invalid string' => ['blablabbla'],
        ];
    }

    /**
     * @dataProvider getDurationCreateFromDateStringFailsProvider
     *
     * @param string $input duration
     */
    public function testDurationCreateFromDateStringFails(string $input): void
    {
        if (!$this->isBugFixedcreateFromDateString()) {
            self::assertEquals(Duration::fromIsoString('PT0S'), Duration::fromDateString($input));

            return;
        }

        self::expectWarning();

        self::assertFalse(Duration::fromDateString($input));
    }

    private function isBugFixedcreateFromDateString(): bool
    {
        return version_compare(PHP_VERSION, '7.3.4', '>=') ||
            (version_compare(PHP_VERSION, '7.2.17', '>=') &&
                version_compare(PHP_VERSION, '7.3', '<'));
    }

    public function getDurationCreateFromDateStringFailsProvider(): iterable
    {
        return [
            'invalid interval spec 1' => ['yolo'],
        ];
    }

    public function testIntervalWithFraction(): void
    {
        $duration = Duration::fromIsoString('PT3.1S');
        self::assertSame('PT3.1S', $this->formatDuration($duration));

        $duration = Duration::fromIsoString('P0000-00-00T00:05:00.023658');
        self::assertSame('PT5M0.023658S', $this->formatDuration($duration));
        self::assertSame(0.023658, $duration->toDateInterval()->f);
    }

    public function testCreateFromTimeStringFails(): void
    {
        $this->expectException(InvalidTimeRange::class);

        Duration::fromTimeString('123');
    }

    /**
     * @dataProvider fromTimeStringProvider
     */
    public function testCreateFromTimeStringSucceeds(string $chronometer, string $expected, int $revert): void
    {
        $duration = Duration::fromTimeString($chronometer);

        self::assertSame($expected, $this->formatDuration($duration));
        self::assertSame($revert, $duration->toDateInterval()->invert);
    }

    public function fromTimeStringProvider(): iterable
    {
        return [
            'hour and minute' => [
                'chronometer' => '1:2',
                'expected' => 'PT1H2M',
                'invert' => 0,
            ],
            'hour, minute, seconds' => [
                'chronometer' => '1:2:3',
                'expected' => 'PT1H2M3S',
                'invert' => 0,
            ],
            'handling 0 prefix' => [
                'chronometer' => '00001:00002:000003.0004',
                'expected' => 'PT1H2M3.0004S',
                'invert' => 0,
            ],
            'negative chrono' => [
                'chronometer' => '-12:28',
                'expected' => 'PT12H28M',
                'invert' => 1,
            ],
            'negative chrono with seconds' => [
                'chronometer' => '-00:00:28.5',
                'expected' => 'PT28.5S',
                'invert' => 1,
            ],
        ];
    }

    /**
     * @dataProvider fromChronoFailsProvider
     */
    public function testCreateFromChronoStringFails(string $input): void
    {
        $this->expectException(InvalidTimeRange::class);

        Duration::fromChronoString($input);
    }

    public function fromChronoFailsProvider(): iterable
    {
        return [
            'invalid string' => ['foobar'],
            'float like string' => ['-28.5'],
        ];
    }

    /**
     * @dataProvider fromChronoProvider
     */
    public function testCreateFromChronoStringSucceeds(string $chronometer, string $expected, int $revert): void
    {
        $duration = Duration::fromChronoString($chronometer);

        self::assertSame($expected, $this->formatDuration($duration));
        self::assertSame($revert, $duration->toDateInterval()->invert);
    }

    /**
     * @dataProvider fromChronoProvider
     */
    public function testCreate(string $chronometer, string $expected, int $revert): void
    {
        $duration = Duration::create($chronometer);

        self::assertSame($expected, $this->formatDuration($duration));
        self::assertSame($revert, $duration->toDateInterval()->invert);
    }

    public function fromChronoProvider(): iterable
    {
        return [
            'minute and seconds' => [
                'chronometer' => '1:2',
                'expected' => 'PT1M2S',
                'invert' => 0,
            ],
            'hour, minute, seconds' => [
                'chronometer' => '1:2:3',
                'expected' => 'PT1H2M3S',
                'invert' => 0,
            ],
            'handling 0 prefix' => [
                'chronometer' => '00001:00002:000003.0004',
                'expected' => 'PT1H2M3.0004S',
                'invert' => 0,
            ],
            'negative chrono' => [
                'chronometer' => '-12:28.5',
                'expected' => 'PT12M28.5S',
                'invert' => 1,
            ],
        ];
    }

    /**
     * @dataProvider adjustedToDataProvider
     *
     * @param mixed $reference_date a valid datepoint
     */
    public function testadjustedTo(string $input, $reference_date, string $expected): void
    {
        $duration = Duration::fromIsoString($input);
        self::assertSame($expected, $this->formatDuration($duration->adjustedTo($reference_date)));
        self::assertSame($expected, $this->formatDuration($duration->adjustedTo($reference_date)));
    }

    public function adjustedToDataProvider(): iterable
    {
        return [
            'nothing to carry over' => [
                'input' => 'PT3H',
                'reference_date' => 0,
                'expected' => 'PT3H',
            ],
            'hour transformed in days' => [
                'input' => 'PT24H',
                'reference_date' => 0,
                'expected' => 'P1D',
            ],
            'days transformed in months' => [
                'input' => 'P31D',
                'reference_date' => 0,
                'expected' => 'P1M',
            ],
            'months transformed in years' => [
                'input' => 'P12M',
                'reference_date' => 0,
                'expected' => 'P1Y',
            ],
            'leap year' => [
                'input' => 'P29D',
                'reference_date' => '2020-02-01',
                'expected' => 'P1M',
            ],
            'none leap year' => [
                'input' => 'P29D',
                'reference_date' => '2019-02-01',
                'expected' => 'P1M1D',
            ],
            'dst day' => [
                'input' => 'PT4H',
                'reference_date' => new DateTime('2019-03-31', new DateTimeZone('Europe/Brussels')),
                'expected' => 'PT3H',
            ],
            'non dst day' => [
                'input' => 'PT4H',
                'reference_date' => new DateTime('2019-04-01', new DateTimeZone('Europe/Brussels')),
                'expected' => 'PT4H',
            ],
        ];
    }
}
