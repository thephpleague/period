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
    case INCLUDE_START_EXCLUDE_END = '[)';
    case INCLUDE_ALL = '[]';
    case EXCLUDE_START_INCLUDE_END = '(]';
    case EXCLUDE_ALL = '()';

    public function isStartIncluded(): bool
    {
        return '[' === $this->value[0];
    }

    public function isEndIncluded(): bool
    {
        return ']' === $this->value[1];
    }

    public function equalsStart(self $other): bool
    {
        return $other->value[0] === $this->value[0];
    }

    public function equalsEnd(self $other): bool
    {
        return $other->value[1] === $this->value[1];
    }

    public function includeStart(): self
    {
        return match ($this) {
            self::EXCLUDE_ALL => self::INCLUDE_START_EXCLUDE_END,
            self::EXCLUDE_START_INCLUDE_END => self::INCLUDE_ALL,
            default => $this,
        };
    }

    public function includeEnd(): self
    {
        return match ($this) {
            self::INCLUDE_START_EXCLUDE_END => self::INCLUDE_ALL,
            self::EXCLUDE_ALL => self::EXCLUDE_START_INCLUDE_END,
            default => $this,
        };
    }

    public function excludeStart(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::EXCLUDE_START_INCLUDE_END,
            self::INCLUDE_START_EXCLUDE_END => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function excludeEnd(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::INCLUDE_START_EXCLUDE_END,
            self::EXCLUDE_START_INCLUDE_END => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function replaceStartWith(self $other): self
    {
        if ($other->value[0] === $this->value[0]) {
            return $this;
        }

        return self::from($other->value[0].$this->value[1]);
    }

    public function replaceEndWith(self $other): self
    {
        if ($other->value[1] === $this->value[1]) {
            return $this;
        }

        return self::from($this->value[0].$other->value[1]);
    }
}
