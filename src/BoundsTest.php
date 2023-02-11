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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BoundsTest extends TestCase
{
    #[DataProvider('boundsIso80000Provider')]
    public function testIso80000(string $notation, Bounds $bounds, string $start, string $end): void
    {
        self::assertSame($notation, $bounds->buildIso80000($start, $end));
    }

    /**
     * @return iterable<array{notation:string, bounds:Bounds, start:string, end:string}>
     */
    public static function boundsIso80000Provider(): iterable
    {
        yield 'exclude all' => [
            'notation' => '(3, 4)',
            'bounds' => Bounds::ExcludeAll,
            'start' => '3',
            'end' => '4',
        ];

        $period = Period::fromMonth(2022, 3);

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04)',
            'bounds' => Bounds::IncludeStartExcludeEnd,
            'start' => $period->startDate->format('Y-m-d'),
            'end' => $period->endDate->format('Y-m'),
        ];
    }

    #[DataProvider('boundsBourbakiProvider')]
    public function testBourbaki(string $notation, Bounds $bounds, string $start, string $end): void
    {
        self::assertSame($notation, $bounds->buildBourbaki($start, $end));
    }

    /**
     * @return iterable<array{notation:string, bounds:Bounds, start:string, end:string}>
     */
    public static function boundsBourbakiProvider(): iterable
    {
        yield 'exclude all' => [
            'notation' => ']3, 4[',
            'bounds' => Bounds::ExcludeAll,
            'start' => '3',
            'end' => '4',
        ];

        $period = Period::fromMonth(2022, 3);

        yield 'include start exclude end' => [
            'notation' => '[2022-03-01, 2022-04[',
            'bounds' => Bounds::IncludeStartExcludeEnd,
            'start' => $period->startDate->format('Y-m-d'),
            'end' => $period->endDate->format('Y-m'),
        ];
    }

    public function testFromIso80000Succeeds(): void
    {
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::IncludeAll], Bounds::parseIso80000('[3,5]'));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::ExcludeAll], Bounds::parseIso80000('(3,5)'));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::ExcludeStartIncludeEnd], Bounds::parseIso80000('(3,5]'));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::IncludeStartExcludeEnd], Bounds::parseIso80000('[3,5)'));
    }

    public function testFromBourbakiSucceeds(): void
    {
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::IncludeAll], Bounds::parseBourbaki('[3,5]'));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::ExcludeAll], Bounds::parseBourbaki(']3,5['));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::ExcludeStartIncludeEnd], Bounds::parseBourbaki(']3,5]'));
        self::assertSame(['start' => '3', 'end' => '5', 'bounds' => Bounds::IncludeStartExcludeEnd], Bounds::parseBourbaki('[3,5['));
    }

    #[DataProvider('fromNotationFailsProvider')]
    public function testFromNotationFails(string $notation): void
    {
        $this->expectException(InvalidInterval::class);

        Bounds::parseIso80000($notation);
    }

    /**
     * @return iterable<string, array{notation:string}>
     */
    public static function fromNotationFailsProvider(): iterable
    {
        yield 'invalid notation' => ['notation' => 'foobar'];
        yield 'mixed notation 1' => ['notation' => ']3,5)'];
        yield 'mixed notation 2' => ['notation' => '([3,5'];
    }
}
