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

use League\Period\Period;
use League\Period\Sequence;

use function array_fill;
use function array_splice;
use function ceil;
use function count;
use function floor;
use function implode;
use function str_pad;
use function str_repeat;

/**
 * A class to output a Dataset via a Gantt Bar Graph.
 */
final class GanttChart implements Chart
{
    private float $start = 0;
    private float $unit = 1;
    /** @var array<string>  */
    private array $emptyLineCharacters;

    public function __construct(
        public readonly GanttChartConfig $config = new GanttChartConfig()
    ) {
    }

    /**
     * @inheritDoc
     *
     * The generated Gantt Bar can be represented like the following but depends on the configuration used
     *
     * A       [--------)
     * B                    [--)
     * C                            [-----)
     * D              [---------------)
     * RESULT         [-)   [--)    [-)
     */
    public function stroke(Data $dataset): void
    {
        $this->setChartScale($dataset);
        $padding = $this->config->labelAlignment->toPadding();
        $gap = str_repeat(' ', $this->config->gapSize);
        $leftMargin = str_repeat(' ', $this->config->leftMarginSize);
        $labelMaxLength = $dataset->labelMaxLength();
        $colorCodeIndexes = $this->config->colors;
        $colorCodeCount = count($colorCodeIndexes);
        $output = $this->config->output;
        $this->emptyLineCharacters = array_fill(0, $this->config->width, $this->config->spaceCharacter);
        foreach ($dataset as $offset => [$label, $sequence]) {
            $output->writeln(
                $leftMargin
                .str_pad((string) $label, $labelMaxLength, ' ', $padding)
                .$gap
                .$this->sequenceToLine($sequence),
                $colorCodeIndexes[$offset % $colorCodeCount]
            );
        }
    }

    /**
     * Sets the scale to render the line.
     */
    private function setChartScale(Data $dataset): void
    {
        $this->start = 0;
        $this->unit = 1;
        $length = $dataset->length();
        if (null !== $length) {
            $this->start = $length->startDate->getTimestamp();
            $this->unit = $this->config->width / $length->timeDuration();
        }
    }

    /**
     * Convert a Dataset item into a graph line.
     *
     * The empty line get filled by characters to create something like this  [--------)
     */
    private function sequenceToLine(Sequence $item): string
    {
        return implode('', $item->reduce($this->periodToCharacters(...), $this->emptyLineCharacters));
    }

    /**
     * @param array<string> $characters
     *
     * @return array<string>
     */
    private function periodToCharacters(array $characters, Period $period): array
    {
        $startIndex = (int) floor(($period->startDate->getTimestamp() - $this->start) * $this->unit);
        $endIndex = (int) ceil(($period->endDate->getTimestamp() - $this->start) * $this->unit);
        $periodLength = $endIndex - $startIndex;

        array_splice($characters, $startIndex, $periodLength, array_fill(0, $periodLength, $this->config->bodyCharacter));
        $characters[$startIndex] = $period->bounds->isStartIncluded() ? $this->config->startIncludedCharacter : $this->config->startExcludedCharacter;
        $characters[$endIndex - 1] = $period->bounds->isEndIncluded() ? $this->config->endIncludedCharacter : $this->config->endExcludedCharacter;

        return $characters;
    }
}
