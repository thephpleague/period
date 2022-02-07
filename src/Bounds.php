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

/*
 * An Enum to handle interval bounds.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   5.0.0
 */
enum Bounds
{
    case INCLUDE_START_EXCLUDE_END;
    case INCLUDE_ALL;
    case EXCLUDE_START_INCLUDE_END;
    case EXCLUDE_ALL;

    /**
     * Parse the ISO 80000 string representation of an interval.
     *
     * @throws InvalidInterval
     *
     * @return array{start:string, end:string, bounds:Bounds}
     */
    public static function parseIso80000(string $notation): array
    {
        static $regexp = '/^(?<lower>\[|\()(?<start>[^,\]\)\[\(]*),(?<end>[^,\]\)\[\(]*)(?<upper>\]|\))$/';
        if (1 !== preg_match($regexp, $notation, $found)) {
            throw InvalidInterval::dueToUnknownNotation('ISO-80000', $notation);
        }

        return [
            'start' => trim($found['start']),
            'end' => trim($found['end']),
            'bounds' => match ($found['lower'].$found['upper']) {
                '[]' => self::INCLUDE_ALL,
                '[)' => self::INCLUDE_START_EXCLUDE_END,
                '()' => self::EXCLUDE_ALL,
                default => self::EXCLUDE_START_INCLUDE_END,
            },
        ];
    }

    /**
     * Parse the Bourbaki string representation of an interval.
     *
     * @throws InvalidInterval
     *
     * @return array{start:string, end:string, bounds:Bounds}
     */
    public static function parseBourbaki(string $notation): array
    {
        static $regexp = '/^(?<lower>\[|\])(?<start>[^,\]\[]*),(?<end>[^,\]\[]*)(?<upper>\[|\])$/';
        if (1 !== preg_match($regexp, $notation, $found)) {
            throw InvalidInterval::dueToUnknownNotation('Bourbaki', $notation);
        }

        return [
            'start' => trim($found['start']),
            'end' => trim($found['end']),
            'bounds' => match ($found['lower'].$found['upper']) {
                '[]' => self::INCLUDE_ALL,
                '[[' => self::INCLUDE_START_EXCLUDE_END,
                '][' => self::EXCLUDE_ALL,
                default => self::EXCLUDE_START_INCLUDE_END,
            },
        ];
    }

    /**
     * Returns the ISO 80000 string representation of an interval.
     */
    public function buildIso80000(string $start, string $end): string
    {
        return match ($this) {
            self::INCLUDE_ALL => "[$start, $end]",
            self::INCLUDE_START_EXCLUDE_END => "[$start, $end)",
            self::EXCLUDE_ALL => "($start, $end)",
            self::EXCLUDE_START_INCLUDE_END => "($start, $end]",
        };
    }

    /**
     * Returns the Bourbaki string representation of an interval.
     */
    public function buildBourbaki(string $start, string $end): string
    {
        return match ($this) {
            self::INCLUDE_ALL => "[$start, $end]",
            self::INCLUDE_START_EXCLUDE_END => "[$start, {$end}[",
            self::EXCLUDE_ALL => "]$start, {$end}[",
            self::EXCLUDE_START_INCLUDE_END => "]$start, $end]",
        };
    }

    public function isStartIncluded(): bool
    {
        return match ($this) {
            self::INCLUDE_START_EXCLUDE_END, self::INCLUDE_ALL => true,
            default => false,
        };
    }

    public function isEndIncluded(): bool
    {
        return match ($this) {
            self::EXCLUDE_START_INCLUDE_END, self::INCLUDE_ALL => true,
            default => false,
        };
    }

    public function equalsStart(self $other): bool
    {
        return match ($this) {
            self::INCLUDE_ALL, self::INCLUDE_START_EXCLUDE_END => $other->isStartIncluded(),
            default => !$other->isStartIncluded(),
        };
    }

    public function equalsEnd(self $other): bool
    {
        return match ($this) {
            self::INCLUDE_ALL, self::EXCLUDE_START_INCLUDE_END => $other->isEndIncluded(),
            default => !$other->isEndIncluded(),
        };
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

    public function replaceStart(self $other): self
    {
        return match (true) {
            $other->isStartIncluded() => $this->includeStart(),
            default => $this->excludeStart(),
        };
    }

    public function replaceEnd(self $other): self
    {
        return match (true) {
            $other->isEndIncluded() => $this->includeEnd(),
            default => $this->excludeEnd(),
        };
    }
}
