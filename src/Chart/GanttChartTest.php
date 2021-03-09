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
use PHPUnit\Framework\TestCase;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \League\Period\Chart\GanttChart
 */
final class GanttChartTest extends TestCase
{
    /**
     * @var \League\Period\Chart\GanttChart
     */
    private $graph;

    /**
     * @var resource
     */
    private $stream;

    public function setUp(): void
    {
        $this->stream = $this->setStream();
        $config = (new GanttChartConfig(new \League\Period\Chart\ConsoleOutput($this->stream)))->withColors('red');
        $this->graph = new GanttChart($config);
    }

    /**
     * @return resource
     */
    private function setStream()
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');

        return $stream;
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $graph = new GanttChart();
        self::assertNotEquals($this->graph, $graph);
    }

    /**
     * @covers ::stroke
     * @covers ::setChartScale
     */
    public function testDisplayEmptyDataset(): void
    {
        $this->graph->stroke(new \League\Period\Chart\Dataset());
        rewind($this->stream);
        $data = stream_get_contents($this->stream);

        self::assertSame('', $data);
    }

    /**
     * @covers ::stroke
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     * @covers \League\Period\Chart\ConsoleOutput
     */
    public function testDisplayPeriods(): void
    {
        $this->graph->stroke(new \League\Period\Chart\Dataset([
            ['A', Period::fromDatepoint('2018-01-01', '2018-01-15')],
            ['B', Period::fromDatepoint('2018-01-15', '2018-02-01')],
        ]));

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }

    /**
     * @covers ::stroke
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     */
    public function testDisplaySequence(): void
    {
        $dataset = new \League\Period\Chart\Dataset([
            ['A', new Sequence(Period::fromDatepoint('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(Period::fromDatepoint('2018-01-15', '2018-02-01'))],
        ]);

        $this->graph->stroke($dataset);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('A [--------------------------)', $data);
        self::assertStringContainsString('B                            [-------------------------------)', $data);
    }

    /**
     * @covers ::stroke
     * @covers ::setChartScale
     * @covers ::drawDataPortion
     */
    public function testDisplayEmptySequence(): void
    {
        $dataset = new Dataset();
        $dataset->append('sequenceA', new Sequence());
        $dataset->append('sequenceB', new Sequence());
        $this->graph->stroke($dataset);

        rewind($this->stream);
        /** @var string $data */
        $data = stream_get_contents($this->stream);

        self::assertStringContainsString('sequenceA                                  ', $data);
        self::assertStringContainsString('sequenceB                                  ', $data);
    }
}
