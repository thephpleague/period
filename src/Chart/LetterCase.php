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

use function mb_strtolower;
use function mb_strtoupper;

enum LetterCase
{
    case Upper;
    case Lower;

    public function isUpper(): bool
    {
        return self::Upper === $this;
    }

    public function convert(string $str): string
    {
        return match ($this) {
            self::Upper => mb_strtoupper($str),
            self::Lower => mb_strtolower($str),
        };
    }
}
