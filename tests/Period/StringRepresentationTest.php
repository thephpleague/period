<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period\Period;

use DateTimeImmutable;
use League\Period\Period;
use LeagueTest\Period\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
class StringRepresentationTest extends TestCase
{
    public function testToString(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = new Period('2014-05-01', '2014-05-08');
        $res = (string) $period;

        self::assertTrue(false !== strpos($res, '2014-04-30T21:00:00'));
        self::assertTrue(false !== strpos($res, '2014-05-07T21:00:00'));
    }

    public function testJsonSerialize(): void
    {
        $period = Period::fromMonth(2015, 4);
        $json = json_encode($period);

        self::assertTrue(false !== $json);
        
        $res = json_decode($json);

        self::assertEquals($period->getStartDate(), new DateTimeImmutable($res->startDate));
        self::assertEquals($period->getEndDate(), new DateTimeImmutable($res->endDate));
    }

    public function testFormat(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        self::assertSame('[2015-04, 2015-05)', Period::fromMonth(2015, 4)->format('Y-m'));
        self::assertSame(
            '[2015-04-01 Africa/Nairobi, 2015-04-01 Africa/Nairobi)',
            (new Period('2015-04-01', '2015-04-01'))->format('Y-m-d e')
        );
    }
}
