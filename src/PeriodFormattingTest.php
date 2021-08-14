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

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
final class PeriodFormattingTest extends TestCase
{
    /** @var string **/
    private $timezone;

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    public function testToString(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = Period::fromDate(new DateTimeImmutable('2014-05-01'), new DateTimeImmutable('2014-05-08'));
        $res = $period->toIso8601();

        self::assertTrue(str_contains($res, '2014-04-30T21:00:00'));
        self::assertTrue(str_contains($res, '2014-05-07T21:00:00'));
    }

    public function testJsonSerialize(): void
    {
        $period = Period::fromMonth(2015, 4);
        $json = json_encode($period);

        self::assertTrue(false !== $json);

        /** @var array{startDate:string, endDate:string, startDateExcluded:bool, endDateExcluded:bool} $res */
        $res = json_decode($json, true);

        self::assertEquals($period->startDate(), new DateTimeImmutable($res['startDate']));
        self::assertEquals($period->endDate(), new DateTimeImmutable($res['endDate']));
        self::assertSame($period->isStartDateExcluded(), $res['startDateExcluded']);
        self::assertSame($period->isEndDateExcluded(), $res['endDateExcluded']);
    }

    public function testFormat(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        self::assertSame('[2015-04, 2015-05)', Period::fromMonth(2015, 4)->toNotation('Y-m'));
        self::assertSame(
            '[2015-04-01 Africa/Nairobi, 2015-04-01 Africa/Nairobi)',
            (Period::fromDate(new DateTimeImmutable('2015-04-01'), new DateTimeImmutable('2015-04-01')))->toNotation('Y-m-d e')
        );
    }
}
