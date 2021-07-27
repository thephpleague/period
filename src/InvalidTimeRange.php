<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DatePeriod;
use InvalidArgumentException;

final class InvalidTimeRange extends InvalidArgumentException implements TimeRangeError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dueToDatepointMismatch(): self
    {
        return new self('The ending datepoint must be greater or equal to the starting datepoint');
    }

    public static function dueToUnknownBoundaries(string $unknownBoundaryType, array $supportedTypes): self
    {
        return new self(
            '`'.$unknownBoundaryType.'` is an unknown or invalid boundary rype. The only valid values are `'.implode('`, `', array_keys($supportedTypes)).'`.',
        );
    }

    public static function dueToNonOverlappingPeriod(): self
    {
        return new self('Both '.Period::class.' objects should overlaps');
    }

    public static function dueToUnknownDurationFormat(string $duration): self
    {
        return new self('Unknown or bad format `'.$duration.'`.');
    }

    public static function dueToInvalidDateFormat(string $format, string $date): self
    {
        return new self('The date notation `'.$date.'` is incompatible with the date format `'.$format.'`.');
    }

    public static function dueToInvalidDatePeriod(): self
    {
        return new self('The '.DatePeriod::class.' should contain an end date to be instantiate a '.Period::class.' class.');
    }

    public static function dueToUnknownNotation(string $notation): self
    {
        return new self('Unknown or unsupported interval notation `'.$notation.'`.');
    }

    public static function dueToUnsupportedNotation(string $notation): self
    {
        return new self('Unsupported interval notation `'.$notation.'`.');
    }

    public static function dueToInvalidFraction(): self
    {
        return new self('The fraction should be a valid positive integer or zero.');
    }
}
