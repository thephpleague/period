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

use DateTimeImmutable;
use DateTimeInterface;

final class UnboundPeriod
{
    private const BOUNDARY_TYPE = [
        self::INCLUDE_START_EXCLUDE_END => 1,
        self::INCLUDE_ALL => 1,
        self::EXCLUDE_START_INCLUDE_END => 1,
        self::EXCLUDE_ALL => 1,
    ];

    public const INCLUDE_START_EXCLUDE_END = '[)';
    public const EXCLUDE_START_INCLUDE_END = '(]';
    public const EXCLUDE_ALL = '()';
    public const INCLUDE_ALL = '[]';

    private DateTimeImmutable|null $startDate;
    private DateTimeImmutable|null $endDate;
    private string $boundaryType;

    /**
     * @param  DateTimeImmutable|null $startDate
     * @param  DateTimeImmutable|null $endDate
     * @throws InvalidTimeRange       If the instance can not be created
     */
    private function __construct(
        DateTimeImmutable|null $startDate,
        DateTimeImmutable|null $endDate,
        string $boundaryType
    ) {
        if (null !== $startDate && null !== $endDate) {
            throw InvalidTimeRange::dueToDatepointMismatch();
        }

        if (!isset(self::BOUNDARY_TYPE[$boundaryType])) {
            throw InvalidTimeRange::dueToInvalidBoundaryType($boundaryType, self::BOUNDARY_TYPE);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->boundaryType = $boundaryType;
    }

    private static function filterDatepoint(mixed $datepoint): DateTimeImmutable
    {
        return match (true) {
            $datepoint instanceof Datepoint => $datepoint->toDateTimeImmutable(),
            $datepoint instanceof DateTimeImmutable => $datepoint,
            $datepoint instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($datepoint),
            false !== ($timestamp = filter_var($datepoint, FILTER_VALIDATE_INT)) => (new DateTimeImmutable())->setTimestamp($datepoint),
            default => new DateTimeImmutable($datepoint),
        };
    }

    /**
     * @inheritDoc
     */
    public static function __set_state(array $interval)
    {
        return new self(
            $interval['startDate'],
            $interval['endDate'],
            $interval['boundaryType'] ?? self::INCLUDE_START_EXCLUDE_END
        );
    }

    /**
     * Creates an unbounded period from its starting datepoint.
     */
    public static function after(mixed $startDate, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(self::filterDatepoint($startDate), null, $boundaryType);
    }

    /**
     * Creates an unbounded period from its ending datepoint.
     */
    public static function before(mixed $endDate, string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(null, self::filterDatepoint($endDate), $boundaryType);
    }

    /**
     * Creates an unbounded period without starting and ending datepoint.
     */
    public static function infinite(string $boundaryType = self::INCLUDE_START_EXCLUDE_END): self
    {
        return new self(null, null, $boundaryType);
    }

    public function getStartDate(): DateTimeImmutable|null
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable|null
    {
        return $this->endDate;
    }

    public function getBoundaryType(): string
    {
        return $this->boundaryType;
    }

    /**
     * Tells whether the unbounded period is infinite.
     */
    public function isInfinite(): bool
    {
        return null === $this->startDate && null === $this->endDate;
    }

    /**
     * Tells whether the start datepoint is included in the boundary.
     */
    public function isStartIncluded(): bool
    {
        return '[' === $this->boundaryType[0];
    }

    /**
     * Tells whether the start datepoint is excluded from the boundary.
     */
    public function isStartExcluded(): bool
    {
        return '(' === $this->boundaryType[0];
    }

    /**
     * Tells whether the end datepoint is included in the boundary.
     */
    public function isEndIncluded(): bool
    {
        return ']' === $this->boundaryType[1];
    }

    /**
     * Tells whether the end datepoint is excluded from the boundary.
     */
    public function isEndExcluded(): bool
    {
        return ')' === $this->boundaryType[1];
    }

    public function durationCompare(Period|self $interval): int
    {
        if ($interval instanceof Period) {
            return 1;
        }

        return 0;
    }

    public function durationEquals(Period|self $interval): bool
    {
        return 0 === $this->durationCompare($interval);
    }

    /**
     * Tells whether the current instance duration is greater than the submitted one.
     */
    public function durationGreaterThan(self $interval): bool
    {
        return 1 === $this->durationCompare($interval);
    }

    /**
     * Tells whether the current instance duration is less than the submitted one.
     */
    public function durationLessThan(self $interval): bool
    {
        return false;
    }
}
