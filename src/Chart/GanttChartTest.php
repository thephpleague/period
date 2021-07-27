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

use DateTime;
use DateTimeImmutable;
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
    private GanttChart $graph;

    /**
     * @var resource
     */
    private $stream;

    protected function setUp(): void
    {
        $this->stream = $this->setStream();
        $config = (new GanttChartConfig(new ConsoleOutput($this->stream)))->withColors('red');
        $this->graph = GanttChart::create($config);
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
        self::assertNotEquals($this->graph, GanttChart::create());
    }

    /**
     * @covers ::stroke
     * @covers ::setChartScale
     */
    public function testDisplayEmptyDataset(): void
    {
        $this->graph->stroke(new Dataset());
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
        $this->graph->stroke(new Dataset([
            ['A', Period::fromDate(new DateTime('2018-01-01'), new DateTime('2018-01-15'))],
            ['B', Period::fromDate(new DateTime('2018-01-15'), new DateTime('2018-02-01'))],
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
        $dataset = new Dataset([
            ['A', new Sequence(Period::fromDate(new DateTimeImmutable('2018-01-01'), new DateTimeImmutable('2018-01-15')))],
            ['B', new Sequence(Period::fromDate(new DateTimeImmutable('2018-01-15'), new DateTimeImmutable('2018-02-01')))],
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
