<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use League\Period\Interval;
use League\Period\Period;

class ProxyIntervalTest extends IntervalTest
{
    protected function createInterval(DateTimeInterface $startDate, DateTimeInterface $endDate): Interval
    {
        $period = new Period($startDate, $endDate);

        return new ProxyPeriod($period, ['foo' => 'bar']);
    }

    public function testIntervalImplementationComparison()
    {
        $proxy = $this->createInterval(new DateTimeImmutable('2012-06-08'), new DateTimeImmutable('2012-07-03'));
        $period = new Period(new DateTimeImmutable('2012-06-08'), new DateTimeImmutable('2012-07-03'));
        self::assertTrue($proxy->equalsTo($period));
    }

    public function testProxyIntervalCreation()
    {
        $proxy = $this->createInterval(new DateTimeImmutable('2012-06-08'), new DateTimeImmutable('2012-07-03'));
        $gapProxy = $this->createInterval(new DateTimeImmutable('2013-06-08'), new DateTimeImmutable('2013-07-03'));
        $overlapsProxy = $this->createInterval(new DateTimeImmutable('2012-06-08'), new DateTimeImmutable('2012-07-01'));

        self::assertInstanceOf(ProxyPeriod::class, $proxy->startingOn(new DateTimeImmutable('2012-06-08')));
        self::assertInstanceOf(ProxyPeriod::class, $proxy->endingOn(new DateTimeImmutable('2012-07-03')));
        self::assertInstanceOf(ProxyPeriod::class, $proxy->move(new DateInterval('P3D')));
        self::assertInstanceOf(ProxyPeriod::class, $proxy->expand(new DateInterval('P13D')));
        self::assertInstanceOf(Interval::class, $proxy->intersect($overlapsProxy));
        self::assertInstanceOf(Interval::class, $proxy->gap($gapProxy));
    }
}
