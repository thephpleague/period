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

use DateTimeImmutable;

final class PeriodFormattingTest extends PeriodTestCase
{
    public function testToString(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = Period::fromDate('2014-05-01', '2014-05-08');
        $format = 'Y-m-d\TH:i:s';

        self::assertSame($period->toIso8601($format), '2014-04-30T21:00:00/2014-05-07T21:00:00');
        self::assertSame($period->toIso80000($format), '[2014-05-01T00:00:00, 2014-05-08T00:00:00)');
        self::assertSame($period->toBourbaki($format), '[2014-05-01T00:00:00, 2014-05-08T00:00:00[');
    }

    public function testJsonSerialize(): void
    {
        $period = Period::fromMonth(2015, 4);
        $json = json_encode($period);

        self::assertTrue(false !== $json);

        /** @var array{startDate:string, endDate:string, startDateIncluded:bool, endDateIncluded:bool} $res */
        $res = json_decode($json, true);

        self::assertEquals($period->startDate, new DateTimeImmutable($res['startDate']));
        self::assertEquals($period->endDate, new DateTimeImmutable($res['endDate']));
        self::assertSame($period->bounds->isStartIncluded(), $res['startDateIncluded']);
        self::assertSame($period->bounds->isEndIncluded(), $res['endDateIncluded']);
    }

    public function testFormat(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        self::assertSame('[2015-04, 2015-05)', Period::fromMonth(2015, 4)->toIso80000('Y-m'));
        self::assertSame(
            '[2015-04-01 Africa/Nairobi, 2015-04-01 Africa/Nairobi)',
            (Period::fromDate('2015-04-01', '2015-04-01'))->toIso80000('Y-m-d e')
        );
    }
}
