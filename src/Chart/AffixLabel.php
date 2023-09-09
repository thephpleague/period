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

namespace League\Period\Chart;

use Iterator;

use function preg_match;

/**
 * A class to attach a prefix and/or a suffix string to the generated label.
 *
 * @see LabelGenerator
 */
final class AffixLabel implements LabelGenerator
{
    public function __construct(
        public readonly LabelGenerator $labelGenerator,
        public readonly string $labelPrefix = '',
        public readonly string $labelSuffix = ''
    ) {
        $this->filterAffix($this->labelPrefix);
        $this->filterAffix($this->labelSuffix);
    }

    private function filterAffix(string $str): void
    {
        if (1 === preg_match("/[\r\n]/", $str)) {
            throw UnableToDrawChart::dueToInvalidLabel($str, $this);
        }
    }

    public function generate(int $nbLabels): Iterator
    {
        foreach ($this->labelGenerator->generate($nbLabels) as $key => $label) {
            yield $key => $this->decorate($label);
        }
    }

    public function format(string $label): string
    {
        return $this->decorate($this->labelGenerator->format($label));
    }

    private function decorate(string $string): string
    {
        return $this->labelPrefix.$string.$this->labelSuffix;
    }
}
