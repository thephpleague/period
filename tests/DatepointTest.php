<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use DateTime;
use DateTimeImmutable;
use League\Period\Datepoint;
use PHPUnit\Framework\TestCase;

class DatepointTest extends TestCase
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

    public function testCreateFromFormat(): void
    {
        self::assertInstanceOf(Datepoint::class, Datepoint::createFromFormat('Y-m-d', '2018-12-01'));
        self::assertFalse(Datepoint::createFromFormat('Y-m-d', 'foobar'));
    }

    public function testCreateFromMutable(): void
    {
        $date = new DateTime();
        self::assertTrue(Datepoint::createFromMutable($date) == DateTimeImmutable::createFromMutable($date));
    }
}
