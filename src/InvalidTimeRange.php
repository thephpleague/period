<?php


namespace League\Period;


final class InvalidTimeRange extends \InvalidArgumentException implements TimeRangeError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dueToDatepointMismatch(): self
    {
        return new self('The ending datepoint must be greater or equal to the starting datepoint');
    }

    public static function dueToInvalidBoundaryType(string $unknownBoundaryType, array $supportedTypes): self
    {
        return new self(
            '`'.$unknownBoundaryType.'` is an unknown or invalid boundary rype. The only valid values are `'.implode('`, `', array_keys($supportedTypes)).'`.',
        );
    }

    public static function dueToNonOverlappingPeriod(): self
    {
        return new self('Both '.Period::class.' objects should overlaps');
    }

    public static function dueToUnknownDuratiomFormnat(string $duration): self
    {
        return new self('Unknown or bad format ('.$duration.')');
    }
}
