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

final class DecimalNumber implements LabelGenerator
{
    /**
     * @var int
     */
    private $int;

    /**
     * New instance.
     */
    public function __construct(int $int = 1)
    {
        if (0 >= $int) {
            $int = 1;
        }

        $this->int = $int;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(int $nbLabels): \Iterator
    {
        if (0 >= $nbLabels) {
            return;
        }

        $count = 0;
        $end = $this->int + $nbLabels;
        $value = $this->int;
        while ($value < $end) {
            yield $count => $this->format((string) $value);

            ++$count;
            ++$value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $label): string
    {
        return $label;
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): int
    {
        return $this->int;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $int): self
    {
        if (0 >= $int) {
            $int = 1;
        }

        if ($int === $this->int) {
            return $this;
        }

        $clone = clone $this;
        $clone->int = $int;

        return $clone;
    }
}
