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

use const STR_PAD_BOTH;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

enum Alignment
{
    case Center;
    case Left;
    case Right;

    public static function fromPadding(int $padding): self
    {
        return match ($padding) {
            STR_PAD_BOTH => self::Center,
            STR_PAD_RIGHT => self::Right,
            STR_PAD_LEFT => self::Left,
            default => throw new InvalidArgumentException('Unknown or unsupported padding value.'),
        };
    }

    public function toPadding(): int
    {
        return match ($this) {
            self::Center => STR_PAD_BOTH,
            self::Right => STR_PAD_RIGHT,
            self::Left => STR_PAD_LEFT,
        };
    }
}
