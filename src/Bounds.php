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

enum Bounds: string
{
    case INCLUDE_LOWER_EXCLUDE_UPPER = '[)';
    case INCLUDE_ALL = '[]';
    case EXCLUDE_LOWER_INCLUDE_UPPER = '(]';
    case EXCLUDE_ALL = '()';

    public function isLowerIncluded(): bool
    {
        return '[' === $this->value[0];
    }

    public function equalsLower(Bounds $other): bool
    {
        return $other->value[0] === $this->value[0];
    }

    public function includeLower(): self
    {
        return match ($this) {
            self::EXCLUDE_ALL => self::INCLUDE_LOWER_EXCLUDE_UPPER,
            self::EXCLUDE_LOWER_INCLUDE_UPPER => self::INCLUDE_ALL,
            default => $this,
        };
    }

    public function excludeLower(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::EXCLUDE_LOWER_INCLUDE_UPPER,
            self::INCLUDE_LOWER_EXCLUDE_UPPER => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function mergeLower(self $other): self
    {
        return self::from($other->value[0].$this->value[1]);
    }

    public function isUpperIncluded(): bool
    {
        return ']' === $this->value[1];
    }

    public function equalsUpper(Bounds $other): bool
    {
        return $other->value[1] === $this->value[1];
    }

    public function includeUpper(): self
    {
        return match ($this) {
            self::INCLUDE_LOWER_EXCLUDE_UPPER => self::INCLUDE_ALL,
            self::EXCLUDE_ALL => self::EXCLUDE_LOWER_INCLUDE_UPPER,
            default => $this,
        };
    }

    public function excludeUpper(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::INCLUDE_LOWER_EXCLUDE_UPPER,
            self::EXCLUDE_LOWER_INCLUDE_UPPER => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function mergeUpper(self $other): self
    {
        return self::from($this->value[0].$other->value[1]);
    }
}
