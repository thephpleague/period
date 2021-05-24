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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use const STDOUT;

/**
 * @coversDefaultClass \League\Period\Chart\GanttChartConfig
 */
final class GanttChartConfigTest extends TestCase
{
    /**
     * @var GanttChartConfig
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new GanttChartConfig();
    }

    public function testNewInstance(): void
    {
        self::assertSame('[', $this->config->startIncluded());
        self::assertSame('(', $this->config->startExcluded());
        self::assertSame(']', $this->config->endIncluded());
        self::assertSame(')', $this->config->endExcluded());
        self::assertSame('-', $this->config->body());
        self::assertSame(' ', $this->config->space());
        self::assertSame(60, $this->config->width());
        self::assertSame(1, $this->config->gapSize());
        self::assertSame(['reset'], $this->config->colors());
        self::assertSame(GanttChartConfig::ALIGN_LEFT, $this->config->labelAlign());
    }

    public function testCreateFromRandom(): void
    {
        $config1 = GanttChartConfig::fromRandom();
        $config2 = GanttChartConfig::fromRainbow();
        self::assertContains($config1->colors()[0], $config2->colors());
    }

    /**
     * @dataProvider widthProvider
     */
    public function testWidth(int $size, int $expected): void
    {
        self::assertSame($expected, $this->config->withWidth($size)->width());
    }

    public function widthProvider(): array
    {
        return [
            '0 size' => [0, 10],
            'negative size' => [-23, 10],
            'basic usage' => [23, 23],
            'default value' => [60, 60],
        ];
    }

    /**
     * @dataProvider providerChars
     */
    public function testBody(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withBody($char)->body());
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withEndExcluded($char)->endExcluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withEndIncluded($char)->endIncluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withStartExcluded($char)->startExcluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withStartIncluded($char)->startIncluded());
    }

    /**
     * @dataProvider providerChars
     */
    public function testSpace(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withSpace($char)->space());
    }

    public function providerChars(): array
    {
        return [
            ['-', '-'],
            ['=', '='],
            ['[', '['],
            [']', ']'],
            [')', ')'],
            ['(', '('],
            [' ', ' '],
            ['#', '#'],
            ["\t", "\t"],
            ['€', '€'],
            ['█', '█'],
            [' ', ' '],
            ['\uD83D\uDE00', '😀'],
        ];
    }

    /**
     * @dataProvider colorsProvider
     */
    public function testColors(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->withColors($char)->colors()[0]);
    }

    public function colorsProvider(): array
    {
        return [
            ['=', 'reset'],
            ['white', 'white'],
        ];
    }

    public function testWithColorsReturnSameInstance(): void
    {
        self::assertSame($this->config, $this->config->withColors());
    }

    public function providerInvalidChars(): array
    {
        return [
            ['coucou'],
            ['\uD83D\uDE00\uD83D\uDE00'],
        ];
    }

    /**
     * @dataProvider providerInvalidChars
     */
    public function testWithHeadBlockThrowsInvalidArgumentException(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->config->withBody($input);
    }
    /**
     * @dataProvider providerGaps
     */
    public function testLeftMargin(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->withLeftMarginSize($gap)->leftMarginSize());
    }

    /**
     * @dataProvider providerGaps
     */
    public function testGap(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->withGapSize($gap)->gapSize());
    }

    public function providerGaps(): iterable
    {
        return [
            'single gap' => [
                'gap' => 1,
                'expected' => 1,
            ],
            'empty gap' => [
                'gap' => 0,
                'expected' => 0,
            ],
            'sequence with invalid chars' => [
                'gap' => -42,
                'expected' => 1,
            ],
        ];
    }

    /**
     * @dataProvider providerPaddings
     */
    public function testPadding(int $padding, int $expected): void
    {
        self::assertSame($expected, $this->config->withLabelAlign($padding)->labelAlign());
    }

    public function providerPaddings(): iterable
    {
        return [
            'default' => [
                'padding' => GanttChartConfig::ALIGN_LEFT,
                'expected' => GanttChartConfig::ALIGN_LEFT,
            ],
            'changing wit a defined config' => [
                'padding' => GanttChartConfig::ALIGN_RIGHT,
                'expected' => GanttChartConfig::ALIGN_RIGHT,
            ],
            'changing wit a unknown config' => [
                'padding' => 42,
                'expected' => GanttChartConfig::ALIGN_LEFT,
            ],
        ];
    }

    public function testWithOutputAlwaysReturnsANewInstance(): void
    {
        $newConfig = $this->config->withOutput(new ConsoleOutput(STDOUT));
        self::assertNotSame($this->config, $newConfig);
        self::assertEquals($newConfig->output(), $this->config->output());
    }
}
