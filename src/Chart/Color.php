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
    case Reset = 'reset';
    case Black = 'black';
    case Red = 'red';
    case Green = 'green';
    case Yellow = 'yellow';
    case Blue = 'blue';
    case Magenta = 'magenta';
    case Cyan = 'cyan';
    case White = 'white';
    case None = 'none';

    public function posix(): string
    {
        return match ($this) {
            self::Reset => '0',
            self::Black => '30',
            self::Red => '31',
            self::Green => '32',
            self::Yellow => '33',
            self::Blue => '34',
            self::Magenta => '35',
            self::Cyan => '36',
            self::White => '37',
            self::None => '',
        };
    }

    /**
     * @return non-empty-array<int, Color>
     */
    public static function rainBow(): array
    {
        return [self::Black, self::Red, self::Green, self::Yellow, self::Blue, self::Magenta, self::Cyan, self::White];
    }
}
