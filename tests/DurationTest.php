<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use League\Period\Duration;
use PHPUnit\Framework\TestCase;

class DurationTest extends TestCase
{
    /**
     * @var string
     */
    protected $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    public function testCreateFromDateString(): void
    {
        $duration = Duration::createFromDateString('+1 DAY');
        self::assertSame(1, $duration->d);
        self::assertFalse($duration->days);
        $altduration = Duration::createFromDateString('foobar');
        self::assertSame(0, $altduration->s);
    }
}
