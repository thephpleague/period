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

namespace League\Period;

use RuntimeException;
use Throwable;

final class UnprocessableInterval extends RuntimeException implements IntervalError
{
    private function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function dueToMissingOverlaps(): self
    {
        return new self('Both '.Period::class.' objects must overlaps.');
    }

    public static function dueToMissingGaps(): UnprocessableInterval
    {
        return new self('Both '.Period::class.' objects must have at least one gap.');
    }
}
