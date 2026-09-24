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
        $sign = 1 === $interval->invert ? '-' : '';

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

            return $sign.$interval->format($dateFormat.('' === $timeFormat ? 'T' : $timeFormat))
                .rtrim(sprintf('%f', $second), '0').'S';
        }

        if (0 !== $interval->s) {
            return $sign.$interval->format($dateFormat.('' === $timeFormat ? 'T' : '').$timeFormat.'%sS');
        }

        if (1 === count($time) && 1 === count($date)) {
            return 'PT0S';
        }

        return $sign.$interval->format($dateFormat.$timeFormat);
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
                'expected' => '-PT3.002345S',
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
                'expected' => '-PT12M28.5S',
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
                'expected' => '-PT12H28M',
            ],
            'negative chrono with seconds' => [
                'chronometer' => '-00:00:28.5',
                'expected' => '-PT28.5S',
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

    /**
     * A sign applies to the whole duration, not just the hour. It used to be
     * lost when the hour was zero, and to negate only the hour otherwise.
     *
     * @param 'fromChronoString'|'fromTimeString' $method
     * @param non-empty-string $input
     * @param non-empty-string $reference
     * @param non-empty-string $expected
     */
    #[DataProvider('signedDurationProvider')]
    public function testSignedStringsApplySignToWholeDuration(
        string $method,
        string $input,
        string $reference,
        string $expected
    ): void {
        $duration = Duration::$method($input); /* @phpstan-ignore-line */
        $date = new DateTimeImmutable($reference);

        self::assertSame($expected, $date->add($duration->dateInterval)->format('Y-m-d H:i:s.u'));
    }

    /**
     * @return iterable<string, array{
     *     method: 'fromChronoString'|'fromTimeString',
     *     input: non-empty-string,
     *     reference: non-empty-string,
     *     expected: non-empty-string
     * }>
     */
    public static function signedDurationProvider(): iterable
    {
        return [
            'chrono negative minute and second without hour' => [
                'method' => 'fromChronoString',
                'input' => '-05:30',
                'reference' => '2021-01-01 00:10:00.000000',
                'expected' => '2021-01-01 00:04:30.000000',
            ],
            'chrono negative hour minute second' => [
                'method' => 'fromChronoString',
                'input' => '-1:05:30',
                'reference' => '2021-01-01 02:00:00.000000',
                'expected' => '2021-01-01 00:54:30.000000',
            ],
            'chrono negative fractional second' => [
                'method' => 'fromChronoString',
                'input' => '-12:28.5',
                'reference' => '2021-01-01 00:20:00.000000',
                'expected' => '2021-01-01 00:07:31.500000',
            ],
            'time negative with zero hour' => [
                'method' => 'fromTimeString',
                'input' => '-00:30',
                'reference' => '2021-01-01 01:00:00.000000',
                'expected' => '2021-01-01 00:30:00.000000',
            ],
            'time negative hour and minute' => [
                'method' => 'fromTimeString',
                'input' => '-01:30',
                'reference' => '2021-01-01 05:00:00.000000',
                'expected' => '2021-01-01 03:30:00.000000',
            ],
        ];
    }

    #[DataProvider('provide_native_durations')]
    public function test_from_native(
        \Time\Duration $native,
        string $expected,
    ): void {
        self::assertEquals(
            $expected,
            $this->formatDuration(Duration::fromNative($native))
        );
    }

    /**
     * @throws \Time\TimeException
     * @return iterable<non-empty-string, array{0: \Time\Duration, 1: non-empty-string}>
     */
    public static function provide_native_durations(): iterable
    {
        yield 'whole seconds' => [
            \Time\Duration::fromSeconds(42, 0),
            'PT42S',
        ];

        yield '499 nanoseconds rounds to zero microseconds' => [
            \Time\Duration::fromSeconds(42, 499),
            'PT42S',
        ];

        yield '500 nanoseconds rounds to one microsecond' => [
            \Time\Duration::fromSeconds(42, 500),
            'PT42S',
        ];

        yield '1499 nanoseconds rounds to one microsecond' => [
            \Time\Duration::fromSeconds(42, 1_499),
            'PT42.000001S',
        ];

        yield '1500 nanoseconds rounds to two microseconds' => [
            \Time\Duration::fromSeconds(42, 1_500),
            'PT42.000001S',
        ];

        yield '999499 nanoseconds rounds to 999 microseconds' => [
            \Time\Duration::fromSeconds(42, 999_499),
            'PT42.000999S',
        ];

        yield '999500 nanoseconds rounds to 1000 microseconds' => [
            \Time\Duration::fromSeconds(42, 999_500),
            'PT42.000999S',
        ];

        yield '999999999 nanoseconds rounds to 1000000 microseconds' => [
            \Time\Duration::fromSeconds(42, 999_999_999),
            'PT42.999999S',
        ];
    }
}
