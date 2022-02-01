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

use PHPUnit\Framework\TestCase;

final class BoundsTest extends TestCase
{
    /**
     * @dataProvider boundsIso80000Provider
     */
    public function testIso80000(string $notation, Bounds $bounds, string|int|float $start, string|int|float $end): void
    {
        self::assertSame($notation, $bounds->toIso80000($start, $end));
    }

    /**
     * @return iterable<array{notation:string, bounds:Bounds, start:float|int|string, end:float|int|string}>
     */
    public function boundsIso80000Provider(): iterable
    {
        yield 'include all' => [
            'notation' => '[3, 4]',
            'bounds' => Bounds::INCLUDE_ALL,
            'start' => 3,
            'end' => 4,
        ];

        yield 'exclude all' => [
            'notation' => '(3, 4)',
            'bounds' => Bounds::EXCLUDE_ALL,
            'start' => '3',
            'end' => '4',
        ];

        yield 'exclude start include end' => [
            'notation' => '(3.01, 4.01]',
            'bounds' => Bounds::EXCLUDE_START_INCLUDE_END,
            'start' => 3.01,
            'end' => 4.01,
        ];

        $period = Period::fromMonth(2022, 3);

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04)',
            'bounds' => Bounds::INCLUDE_START_EXCLUDE_END,
            'start' => $period->startDate->format('Y-m-d'),
            'end' => $period->endDate->format('Y-m'),
        ];
    }
    /**
     * @dataProvider boundsBourbakiProvider
     */
    public function testBourbaki(string $notation, Bounds $bounds, string|int|float $start, string|int|float $end): void
    {
        self::assertSame($notation, $bounds->toBourbaki($start, $end));
    }

    /**
     * @return iterable<array{notation:string, bounds:Bounds, start:float|int|string, end:float|int|string}>
     */
    public function boundsBourbakiProvider(): iterable
    {
        yield 'include all' => [
            'notation' => '[3, 4]',
            'bounds' => Bounds::INCLUDE_ALL,
            'start' => 3,
            'end' => 4,
        ];

        yield 'exclude all' => [
            'notation' => ']3, 4[',
            'bounds' => Bounds::EXCLUDE_ALL,
            'start' => '3',
            'end' => '4',
        ];

        yield 'exclude start include end' => [
            'notation' => ']3.01, 4.01]',
            'bounds' => Bounds::EXCLUDE_START_INCLUDE_END,
            'start' => 3.01,
            'end' => 4.01,
        ];

        $period = Period::fromMonth(2022, 3);

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04[',
            'bounds' => Bounds::INCLUDE_START_EXCLUDE_END,
            'start' => $period->startDate->format('Y-m-d'),
            'end' => $period->endDate->format('Y-m'),
        ];
    }

    public function testFromNotationSucceeds(): void
    {
        self::assertSame(Bounds::INCLUDE_ALL, Bounds::fromNotation('[]'));
        self::assertSame(Bounds::EXCLUDE_ALL, Bounds::fromNotation(']['));
        self::assertSame(Bounds::EXCLUDE_ALL, Bounds::fromNotation('()'));
        self::assertSame(Bounds::EXCLUDE_START_INCLUDE_END, Bounds::fromNotation(']]'));
        self::assertSame(Bounds::EXCLUDE_START_INCLUDE_END, Bounds::fromNotation('(]'));
        self::assertSame(Bounds::INCLUDE_START_EXCLUDE_END, Bounds::fromNotation('[['));
        self::assertSame(Bounds::INCLUDE_START_EXCLUDE_END, Bounds::fromNotation('[)'));
    }

    /**
     * @dataProvider fromNotationFailsProvider
     */
    public function testFromNotationFails(string $notation): void
    {
        $this->expectException(DateRangeInvalid::class);

        Bounds::fromNotation($notation);
    }

    public function fromNotationFailsProvider(): iterable
    {
        yield 'invalid notation' => ['notation' => 'foobar'];
        yield 'mixed notation 1' => ['notation' => '])'];
        yield 'mixed notation 2' => ['notation' => '(['];
    }
}
