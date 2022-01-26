<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period\Chart;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AlignmentTest extends TestCase
{
    public function testItCanBeConstructFromPadding(): void
    {
        self::assertSame(Alignment::LEFT, Alignment::fromPadding(STR_PAD_LEFT));
        self::assertSame(Alignment::RIGHT, Alignment::fromPadding(STR_PAD_RIGHT));
        self::assertSame(Alignment::CENTER, Alignment::fromPadding(STR_PAD_BOTH));
    }

    public function testItWillFailOnUnknownPadding(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Alignment::fromPadding(42);
    }

    public function testItCanBeConvertedToPadding(): void
    {
        self::assertSame(STR_PAD_LEFT, Alignment::LEFT->padding());
        self::assertSame(STR_PAD_RIGHT, Alignment::RIGHT->padding());
        self::assertSame(STR_PAD_BOTH, Alignment::CENTER->padding());
    }
}
