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
use function preg_replace;

/**
 * A class to attach a prefix and/or a suffix string to the generated label.
 *
 * @see LabelGenerator
 */
final class AffixLabel implements LabelGenerator
{
    public readonly string $prefix;
    public readonly string $suffix;

    public function __construct(private LabelGenerator $labelGenerator, string $prefix = '', string $suffix = '')
    {
        $this->prefix = $this->filterString($prefix);
        $this->suffix = $this->filterString($suffix);
    }

    private function filterString(string $str): string
    {
        return (string) preg_replace("/[\r\n]/", '', $str);
    }


    public function generate(int $nbLabels): Iterator
    {
        foreach ($this->labelGenerator->generate($nbLabels) as $key => $label) {
            yield $key => $this->prefix.$label.$this->suffix;
        }
    }

    public function format(string $label): string
    {
        return $this->prefix.$this->labelGenerator->format($label).$this->suffix;
    }

    /**
     * Returns an instance with the suffix.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the suffix.
     */
    public function suffix(string $suffix): self
    {
        $suffix = $this->filterString($suffix);
        if ($suffix === $this->suffix) {
            return $this;
        }

        return new self($this->labelGenerator, $this->prefix, $suffix);
    }

    /**
     * Return an instance with the prefix.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the prefix.
     */
    public function prefix(string $prefix): self
    {
        $prefix = $this->filterString($prefix);
        if ($prefix === $this->prefix) {
            return $this;
        }

        return new self($this->labelGenerator, $prefix, $this->suffix);
    }
}
