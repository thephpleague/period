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

enum Color: string
{
    case RESET = 'reset';
    case BLACK = 'black';
    case RED = 'red';
    case GREEN = 'green';
    case YELLOW = 'yellow';
    case BLUE = 'blue';
    case MAGENTA = 'magenta';
    case CYAN = 'cyan';
    case WHITE = 'white';
    case NONE = 'none';

    public function posix(): string
    {
        return match ($this) {
            self::RESET => '0',
            self::BLACK => '30',
            self::RED => '31',
            self::GREEN => '32',
            self::YELLOW => '33',
            self::BLUE => '34',
            self::MAGENTA => '35',
            self::CYAN => '36',
            self::WHITE => '37',
            self::NONE => '',
        };
    }

    /**
     * @return non-empty-array<int, Color>
     */
    public static function rainBow(): array
    {
        return [self::BLACK, self::RED, self::GREEN, self::YELLOW, self::BLUE, self::MAGENTA, self::CYAN, self::WHITE];
    }
}
