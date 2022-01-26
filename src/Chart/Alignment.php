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
    case CENTER;
    case LEFT;
    case RIGHT;

    public static function fromPadding(int $padding): self
    {
        return match ($padding) {
            STR_PAD_BOTH => self::CENTER,
            STR_PAD_RIGHT => self::RIGHT,
            STR_PAD_LEFT => self::LEFT,
            default => throw new InvalidArgumentException('Unknown or unsupported padding value.'),
        };
    }

    public function padding(): int
    {
        return match ($this) {
            self::CENTER => STR_PAD_BOTH,
            self::RIGHT => STR_PAD_RIGHT,
            self::LEFT => STR_PAD_LEFT,
        };
    }
}
