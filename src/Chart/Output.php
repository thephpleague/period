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

namespace League\Period\Chart;

interface Output
{
    public const COLOR_DEFAULT = 'reset';
    public const COLOR_BLACK = 'black';
    public const COLOR_RED = 'red';
    public const COLOR_GREEN = 'green';
    public const COLOR_YELLOW = 'yellow';
    public const COLOR_BLUE = 'blue';
    public const COLOR_MAGENTA = 'magenta';
    public const COLOR_CYAN = 'cyan';
    public const COLOR_WHITE = 'white';
    public const COLORS = [
        self::COLOR_DEFAULT,
        self::COLOR_BLACK,
        self::COLOR_RED,
        self::COLOR_GREEN,
        self::COLOR_YELLOW,
        self::COLOR_BLUE,
        self::COLOR_MAGENTA,
        self::COLOR_CYAN,
        self::COLOR_WHITE,
    ];

    public function writeln(string $message, string $color = self::COLOR_DEFAULT): void;
}
