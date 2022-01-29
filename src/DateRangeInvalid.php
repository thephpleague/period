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

final class DateRangeInvalid extends InvalidArgumentException implements DateRangeError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dueToDatePointMismatch(): self
    {
        return new self('The ending date endpoint must be greater or equal to the starting date endpoint.');
    }

    public static function dueToInvalidDateFormat(string $format, string $date): self
    {
        return new self('The date notation `'.$date.'` is incompatible with the date format `'.$format.'`.');
    }

    public static function dueToInvalidDatePeriod(): self
    {
        return new self('The '.DatePeriod::class.' should contain an end date to be instantiate a '.Period::class.' class.');
    }

    public static function dueToUnknownBounds(string $bounds): self
    {
        return new self('Unknown or unsupported interval bounds `'.$bounds.'`.');
    }

    public static function dueToUnknownNotation(string $notation): self
    {
        return new self('Unknown or unsupported interval notation `'.$notation.'`.');
    }
}
