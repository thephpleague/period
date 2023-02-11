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

namespace League\Period\Chart;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AlignmentTest extends TestCase
{
    public function testItCanBeConstructFromPadding(): void
    {
        self::assertSame(Alignment::Left, Alignment::fromPadding(STR_PAD_LEFT));
        self::assertSame(Alignment::Right, Alignment::fromPadding(STR_PAD_RIGHT));
        self::assertSame(Alignment::Center, Alignment::fromPadding(STR_PAD_BOTH));
    }

    public function testItWillFailOnUnknownPadding(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Alignment::fromPadding(42);
    }

    public function testItCanBeConvertedToPadding(): void
    {
        self::assertSame(STR_PAD_LEFT, Alignment::Left->toPadding());
        self::assertSame(STR_PAD_RIGHT, Alignment::Right->toPadding());
        self::assertSame(STR_PAD_BOTH, Alignment::Center->toPadding());
    }
}
