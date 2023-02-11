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
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

final class DurationTest extends TestCase
{
    private string $timezone;

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    private function formatDuration(Duration $duration): string
    {
        $interval = $duration->dateInterval;

        $date = ['P'];
        if (0 !== $interval->y) {
            $date[] = '%yY';
        }

        if (0 !== $interval->m) {
            $date[] = '%mM';
        }

        if (0 !== $interval->d) {
            $date[] = '%dD';
        }

        $time = ['T'];
        if (0 !== $interval->h) {
            $time[] = '%hH';
        }

        if (0 !== $interval->i) {
            $time[] = '%iM';
        }

        $dateFormat = implode('', $date);
        $timeFormat = 1 === count($time) ? '' : implode('', $time);

        if (0.0 !== $interval->f) {
            $second = $interval->s + $interval->f;
            if (0 > $interval->s) {
                $second = $interval->s - $interval->f;
            }

            return $interval->format($dateFormat.('' === $timeFormat ? 'T' : $timeFormat))
                .rtrim(sprintf('%f', $second), '0').'S';
        }

        if (0 !== $interval->s) {
            return $interval->format($dateFormat.$timeFormat.'%sS');
        }

        if (1 === count($time) && 1 === count($date)) {
            return 'PT0S';
        }

        return $interval->format($dateFormat.$timeFormat);
    }

    public function testInstantiationFromSetState(): void
    {
        $duration = Duration::fromDateInterval(new DateInterval('P1D'));
        /** @var Duration $generatedDuration */
        $generatedDuration = eval('return '.var_export($duration, true).';');

        self::assertEquals($duration, $generatedDuration);
    }

    public function testCreateFromDateInterval(): void
    {
        $duration = Duration::fromDateInterval(new DateInterval('P1D'));

        self::assertSame(1, $duration->dateInterval->d);
        self::assertFalse($duration->dateInterval->days);
    }

    public function testCreateFromDateString(): void
    {
        $duration = Duration::fromDateString('+1 DAY');

        self::assertSame(1, $duration->dateInterval->d);
        self::assertFalse($duration->dateInterval->days);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function getDurationCreateFailsProvider(): iterable
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

    #[DataProvider('getDurationFromSecondsSuccessfulProvider')]
    public function testCreateFromSeconds(int $seconds, int $fraction, string $expected): void
    {
        self::assertSame($expected, $this->formatDuration(Duration::fromSeconds($seconds, $fraction)));
    }

    /**
     * @return array<string, array{seconds:int, fraction:int, expected:string}>
     */
    public static function getDurationFromSecondsSuccessfulProvider(): array
    {
        return [
            'from an integer' => [
                'seconds' => 0,
                'fraction' => 0,
                'expected' => 'PT0S',
            ],
            'negative seconds' => [
                'seconds' => -3,
                'fraction' => 2345,
                'expected' => 'PT-3.002345S',
            ],
        ];
    }

    public function testItFailsToCreateADurationWithANegativeFraction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::fromSeconds(32, -1);
    }

    #[DataProvider('providesValidIsoString')]
    public function testIntervalWithFraction(string $input, string $expected): void
    {
        self::assertSame($expected, $this->formatDuration(Duration::fromIsoString($input)));
    }

    /**
     * @return iterable<string, array{input:string, expected:string}>
     */
    public static function providesValidIsoString(): iterable
    {
        return [
            'IsoString with fraction v1' => [
                'input' => 'PT3.1S',
                'expected' => 'PT3.1S',
            ],
            'IsoString with fraction v2' => [
                'input' => 'P0000-00-00T00:05:00.023658',
                'expected' => 'PT5M0.023658S',
            ],
            'IsoString with fraction v3' => [
                'input' => 'PT5M23658F',
                'expected' => 'PT5M0.023658S',
            ],
        ];
    }

    #[DataProvider('fromChronoFailsProvider')]
    public function testCreateFromChronoStringFails(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::fromChronoString($input);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function fromChronoFailsProvider(): iterable
    {
        return [
            'invalid string' => ['foobar'],
            'float like string' => ['-28.5'],
        ];
    }

    #[DataProvider('fromChronoProvider')]
    public function testCreateFromChronoStringSucceeds(string $chronometer, string $expected): void
    {
        $duration = Duration::fromChronoString($chronometer);

        self::assertSame($expected, $this->formatDuration($duration));
    }

    /**
     * @return iterable<string, array{chronometer:string, expected:string}>
     */
    public static function fromChronoProvider(): iterable
    {
        return [
            'minute and seconds' => [
                'chronometer' => '1:2',
                'expected' => 'PT1M2S',
            ],
            'hour, minute, seconds' => [
                'chronometer' => '1:2:3',
                'expected' => 'PT1H2M3S',
            ],
            'handling 0 prefix' => [
                'chronometer' => '00001:00002:000003.0004',
                'expected' => 'PT1H2M3.0004S',
            ],
            'negative chrono' => [
                'chronometer' => '-12:28.5',
                'expected' => 'PT12M28.5S',
            ],
        ];
    }

    public function testCreateFromTimeStringFails(): void
    {
        $this->expectException(Throwable::class);

        Duration::fromTimeString('123');
    }

    #[DataProvider('fromTimeStringProvider')]
    public function testCreateFromTimeStringSucceeds(string $chronometer, string $expected): void
    {
        $duration = Duration::fromTimeString($chronometer);

        self::assertSame($expected, $this->formatDuration($duration));
    }

    /**
     * @return array<array{chronometer:string, expected:string}>
     */
    public static function fromTimeStringProvider(): iterable
    {
        return [
            'hour and minute' => [
                'chronometer' => '1:2',
                'expected' => 'PT1H2M',
            ],
            'hour, minute, seconds' => [
                'chronometer' => '1:2:3',
                'expected' => 'PT1H2M3S',
            ],
            'handling 0 prefix' => [
                'chronometer' => '00001:00002:000003.0004',
                'expected' => 'PT1H2M3.0004S',
            ],
            'negative chrono' => [
                'chronometer' => '-12:28',
                'expected' => 'PT-12H28M',
            ],
            'negative chrono with seconds' => [
                'chronometer' => '-00:00:28.5',
                'expected' => 'PT28.5S',
            ],
        ];
    }

    #[DataProvider('adjustedToDataProvider')]
    public function testAdjustedTo(string $input, int|string|DateTimeInterface $reference_date, string $expected): void
    {
        $duration = Duration::fromIsoString($input);
        /** @var DateTimeInterface $date */
        $date = match (true) {
            is_int($reference_date) => DatePoint::fromTimestamp($reference_date)->date,
            is_string($reference_date) => DatePoint::fromDateString($reference_date)->date,
            default  => $reference_date,
        };

        self::assertSame($expected, $this->formatDuration($duration->adjustedTo($date)));
    }

    /**
     * @return iterable<string, array{input:string, reference_date:int|string|DateTimeInterface, expected:string}>
     */
    public static function adjustedToDataProvider(): iterable
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
            ], /* THIS IS FIXED AS OF PHP8.1
            'dst day' => [
                'input' => 'PT4H',
                'reference_date' => new DateTime('2019-03-31', new DateTimeZone('Europe/Brussels')),
                'expected' => 'PT3H',
            ],*/
            'non dst day' => [
                'input' => 'PT4H',
                'reference_date' => new DateTime('2019-04-01', new DateTimeZone('Europe/Brussels')),
                'expected' => 'PT4H',
            ],
        ];
    }
}
