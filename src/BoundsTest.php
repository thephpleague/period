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
    public function testIso80000(string $notation, Period $period): void
    {
        self::assertSame(
            $notation,
            $period->bounds->toIso80000($period->startDate->format('Y-m-d'), $period->endDate->format('Y-m-d'))
        );
    }

    /**
     * @return iterable<array{notation:string, period:Period}>
     */
    public function boundsIso80000Provider(): iterable
    {
        yield 'include all' => [
            'notation' => '[2022-03-01, 2022-04-01]',
            'period' => Period::fromMonth(2022, 3, Bounds::INCLUDE_ALL),
        ];

        yield 'exclude all' => [
            'notation' => '(2022-03-01, 2022-04-01)',
            'period' => Period::fromMonth(2022, 3, Bounds::EXCLUDE_ALL),
        ];

        yield 'exclude start include end' => [
            'notation' => '(2022-03-01, 2022-04-01]',
            'period' => Period::fromMonth(2022, 3, Bounds::EXCLUDE_START_INCLUDE_END),
        ];

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04-01)',
            'period' => Period::fromMonth(2022, 3),
        ];
    }
    /**
     * @dataProvider boundsBourbakiProvider
     */
    public function testBourbaki(string $notation, Period $period): void
    {
        self::assertSame(
            $notation,
            $period->bounds->toBourbaki($period->startDate->format('Y-m-d'), $period->endDate->format('Y-m-d'))
        );
    }

    /**
     * @return iterable<array{notation:string, period:Period}>
     */
    public function boundsBourbakiProvider(): iterable
    {
        yield 'include all' => [
            'notation' => '[2022-03-01, 2022-04-01]',
            'period' => Period::fromMonth(2022, 3, Bounds::INCLUDE_ALL),
        ];

        yield 'exclude all' => [
            'notation' => ']2022-03-01, 2022-04-01[',
            'period' => Period::fromMonth(2022, 3, Bounds::EXCLUDE_ALL),
        ];

        yield 'exclude start include end' => [
            'notation' => ']2022-03-01, 2022-04-01]',
            'period' => Period::fromMonth(2022, 3, Bounds::EXCLUDE_START_INCLUDE_END),
        ];

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04-01[',
            'period' => Period::fromMonth(2022, 3),
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
