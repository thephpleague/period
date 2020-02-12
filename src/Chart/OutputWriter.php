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

interface OutputWriter
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
        OutputWriter::COLOR_DEFAULT,
        OutputWriter::COLOR_BLACK,
        OutputWriter::COLOR_RED,
        OutputWriter::COLOR_GREEN,
        OutputWriter::COLOR_YELLOW,
        OutputWriter::COLOR_BLUE,
        OutputWriter::COLOR_MAGENTA,
        OutputWriter::COLOR_CYAN,
        OutputWriter::COLOR_WHITE,
    ];

    public function writeln(string $message, string $color = self::COLOR_DEFAULT): void;
}
