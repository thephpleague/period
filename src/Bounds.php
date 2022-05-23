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

use function preg_match;
use function trim;

/*
 * An Enum to handle interval bounds.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   5.0.0
 */
enum Bounds
{
    case IncludeStartExcludeEnd;
    case IncludeAll;
    case ExcludeStartIncludeEnd;
    case ExcludeAll;

    private const REGEXP_ISO80000 = '/^(?<lower>\[|\()(?<start>[^,\]\)\[\(]*),(?<end>[^,\]\)\[\(]*)(?<upper>\]|\))$/';
    private const REGEXP_BOURBAKI = '/^(?<lower>\[|\])(?<start>[^,\]\[]*),(?<end>[^,\]\[]*)(?<upper>\[|\])$/';

    /**
     * Parse the ISO 80000 string representation of an interval.
     *
     * @throws InvalidInterval
     *
     * @return array{start:string, end:string, bounds:Bounds}
     */
    public static function parseIso80000(string $notation): array
    {
        if (1 !== preg_match(self::REGEXP_ISO80000, $notation, $found)) {
            throw InvalidInterval::dueToUnknownNotation('ISO-80000', $notation);
        }

        return [
            'start' => trim($found['start']),
            'end' => trim($found['end']),
            'bounds' => match ($found['lower'].$found['upper']) {
                '[]' => self::IncludeAll,
                '[)' => self::IncludeStartExcludeEnd,
                '()' => self::ExcludeAll,
                default => self::ExcludeStartIncludeEnd,
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
        if (1 !== preg_match(self::REGEXP_BOURBAKI, $notation, $found)) {
            throw InvalidInterval::dueToUnknownNotation('Bourbaki', $notation);
        }

        return [
            'start' => trim($found['start']),
            'end' => trim($found['end']),
            'bounds' => match ($found['lower'].$found['upper']) {
                '[]' => self::IncludeAll,
                '[[' => self::IncludeStartExcludeEnd,
                '][' => self::ExcludeAll,
                default => self::ExcludeStartIncludeEnd,
            },
        ];
    }

    /**
     * Returns the ISO 80000 string representation of an interval.
     */
    public function buildIso80000(string $start, string $end): string
    {
        return match ($this) {
            self::IncludeAll => "[$start, $end]",
            self::IncludeStartExcludeEnd => "[$start, $end)",
            self::ExcludeAll => "($start, $end)",
            self::ExcludeStartIncludeEnd => "($start, $end]",
        };
    }

    /**
     * Returns the Bourbaki string representation of an interval.
     */
    public function buildBourbaki(string $start, string $end): string
    {
        return match ($this) {
            self::IncludeAll => '['.$start.', '.$end.']',
            self::IncludeStartExcludeEnd => '['.$start.', '.$end.'[',
            self::ExcludeAll => ']'.$start.', '.$end.'[',
            self::ExcludeStartIncludeEnd => ']'.$start.', '.$end.']',
        };
    }

    public function isStartIncluded(): bool
    {
        return match ($this) {
            self::IncludeStartExcludeEnd, self::IncludeAll => true,
            default => false,
        };
    }

    public function isEndIncluded(): bool
    {
        return match ($this) {
            self::ExcludeStartIncludeEnd, self::IncludeAll => true,
            default => false,
        };
    }

    public function equalsStart(self $other): bool
    {
        return match ($this) {
            self::IncludeAll, self::IncludeStartExcludeEnd => $other->isStartIncluded(),
            default => !$other->isStartIncluded(),
        };
    }

    public function equalsEnd(self $other): bool
    {
        return match ($this) {
            self::IncludeAll, self::ExcludeStartIncludeEnd => $other->isEndIncluded(),
            default => !$other->isEndIncluded(),
        };
    }

    public function includeStart(): self
    {
        return match ($this) {
            self::ExcludeAll => self::IncludeStartExcludeEnd,
            self::ExcludeStartIncludeEnd => self::IncludeAll,
            default => $this,
        };
    }

    public function includeEnd(): self
    {
        return match ($this) {
            self::IncludeStartExcludeEnd => self::IncludeAll,
            self::ExcludeAll => self::ExcludeStartIncludeEnd,
            default => $this,
        };
    }

    public function excludeStart(): self
    {
        return match ($this) {
            self::IncludeAll => self::ExcludeStartIncludeEnd,
            self::IncludeStartExcludeEnd => self::ExcludeAll,
            default => $this,
        };
    }

    public function excludeEnd(): self
    {
        return match ($this) {
            self::IncludeAll => self::IncludeStartExcludeEnd,
            self::ExcludeStartIncludeEnd => self::ExcludeAll,
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
