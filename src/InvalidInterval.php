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
use Throwable;

final class InvalidInterval extends InvalidArgumentException implements IntervalError
{
    private function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function dueToEndPointMismatch(): self
    {
        return new self('The ending endpoint must be greater or equal to the starting endpoint.');
    }

    public static function dueToInvalidDateFormat(string $format, string $date, ?Throwable $throwable = null): self
    {
        return new self('The date notation `'.$date.'` is incompatible with the date format `'.$format.'`.', 0, $throwable);
    }

    public static function dueToInvalidRelativeDateFormat(string $endDate, string $startDate): self
    {
        return new self('The end date notation `'.$endDate.'` is incompatible with the start date notation `'.$startDate.'`.');
    }

    public static function dueToInvalidDatePeriod(): self
    {
        return new self('The '.DatePeriod::class.' should contain an end date to instantiate a '.Period::class.' class.');
    }

    public static function dueToUnsupportedVersion(string $method, string $phpVersion): self
    {
        return new self("The `$method` is available starting with `$phpVersion`.");
    }

    public static function dueToUnknownNotation(string $expectedFormat, string $notation): self
    {
        return new self('Unknown or unsupported interval notation `'.$notation.'` for `'.$expectedFormat.'`.');
    }
}
