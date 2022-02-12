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

use InvalidArgumentException;

final class UnableToDrawChart extends InvalidArgumentException implements ChartError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dueToInvalidPattern(string $pattern): self
    {
        return new self('The `'.$pattern.'` pattern must be a single character');
    }

    public static function dueToInvalidUnicodeChar(string $character): self
    {
        return new self('The given string `'.$character.'` is not a valid unicode string');
    }

    public static function dueToInvalidLabel(string|int|float $character, LabelGenerator $labelGenerator): self
    {
        return new self('The given string `'.$character.'` can not be used to generate labels with `'.$labelGenerator::class.'`.');
    }
}
