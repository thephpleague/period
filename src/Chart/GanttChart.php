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
 * A class to output a Dataset via a Gantt Bar graph.
 */
final class GanttChart implements Chart
{
    /**
     * @var GanttChartConfig
     */
    private $config;

    /**
     * @var float
     */
    private $start;

    /**
     * @var float
     */
    private $unit;

    /**
     * New instance.
     *
     * @param ?GanttChartConfig $config
     */
    public function __construct(?GanttChartConfig $config = null)
    {
        $this->config = $config ?? new GanttChartConfig();
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
        $padding = $this->config->labelAlign();
        $gap = str_repeat(' ', $this->config->gapSize());
        $leftMargin = str_repeat(' ', $this->config->leftMarginSize());
        $lineCharacters = array_fill(0, $this->config->width(), $this->config->space());
        $labelMaxLength = $dataset->labelMaxLength();
        $colorCodeIndexes = $this->config->colors();
        $colorCodeCount = count($colorCodeIndexes);
        $output = $this->config->output();
        foreach ($dataset as $offset => [$label, $item]) {
            $colorIndex = $colorCodeIndexes[$offset % $colorCodeCount];
            $labelPortion = str_pad($label, $labelMaxLength, ' ', $padding);
            $dataPortion = $this->drawDataPortion($item, $lineCharacters);
            $output->writeln($leftMargin.$labelPortion.$gap.$dataPortion, $colorIndex);
        }
    }

    /**
     * Sets the scale to render the line.
     */
    private function setChartScale(Data $dataset): void
    {
        $this->start = 0;
        $this->unit = 1;
        $boundaries = $dataset->boundaries();
        if (null !== $boundaries) {
            $this->start = $boundaries->getStartDate()->getTimestamp();
            $this->unit = $this->config->width() / $boundaries->timeDuration();
        }
    }

    /**
     * Convert a Dataset item into a graph data portion.
     *
     * @param string[] $lineCharacters
     */
    private function drawDataPortion(Sequence $item, array $lineCharacters): string
    {
        $reducer = function (array $lineCharacters, Period $period): array {
            $startIndex = (int) floor(($period->getStartDate()->getTimestamp() - $this->start) * $this->unit);
            $endIndex = (int) ceil(($period->getEndDate()->getTimestamp() - $this->start) * $this->unit);
            $periodLength = $endIndex - $startIndex;

            array_splice($lineCharacters, $startIndex, $periodLength, array_fill(0, $periodLength, $this->config->body()));
            $lineCharacters[$startIndex] = $period->isStartIncluded() ? $this->config->startIncluded() : $this->config->startExcluded();
            $lineCharacters[$endIndex - 1] = $period->isEndIncluded() ? $this->config->endIncluded() : $this->config->endExcluded();

            return $lineCharacters;
        };

        return implode('', $item->reduce($reducer, $lineCharacters));
    }
}
