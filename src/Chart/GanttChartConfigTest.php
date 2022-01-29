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
    private GanttChartConfig $config;

    protected function setUp(): void
    {
        $this->config = GanttChartConfig::fromStream(STDOUT);
    }

    public function testNewInstance(): void
    {
        self::assertSame('[', $this->config->startIncludedCharacter);
        self::assertSame('(', $this->config->startExcludedCharacter);
        self::assertSame(']', $this->config->endIncludedCharacter);
        self::assertSame(')', $this->config->endExcludedCharacter);
        self::assertSame('-', $this->config->bodyCharacter);
        self::assertSame(' ', $this->config->spaceCharacter);
        self::assertSame(60, $this->config->width);
        self::assertSame(1, $this->config->gapSize);
        self::assertSame([Color::RESET], $this->config->colors);
        self::assertSame(Alignment::LEFT, $this->config->labelAlignment);
    }

    public function testCreateFromRandom(): void
    {
        $config1 = GanttChartConfig::fromRandomColor();
        $config2 = GanttChartConfig::fromRainbow();
        self::assertContains($config1->colors[0], $config2->colors);
    }

    /**
     * @dataProvider widthProvider
     */
    public function testWidth(int $size, int $expected): void
    {
        self::assertSame($expected, $this->config->width($size)->width);
    }

    /**
     * @return array<string, array{0:int, 1:int}>
     */
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
        self::assertSame($expected, $this->config->bodyCharacter($char)->bodyCharacter);
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->endExcludedCharacter($char)->endExcludedCharacter);
    }

    /**
     * @dataProvider providerChars
     */
    public function testEndIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->endIncludedCharacter($char)->endIncludedCharacter);
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->startExcludedCharacter($char)->startExcludedCharacter);
    }

    /**
     * @dataProvider providerChars
     */
    public function testStartIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->startIncludedCharacter($char)->startIncludedCharacter);
    }

    /**
     * @dataProvider providerChars
     */
    public function testSpace(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->spaceCharacter($char)->spaceCharacter);
    }

    /**
     * @return array<array{0:string, 1:string}>
     */
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
    public function testColors(Color $char, Color $expected): void
    {
        self::assertSame($expected, $this->config->colors($char)->colors[0]);
    }

    /**
     * @return array<array{0:Color, 1:Color}>
     */
    public function colorsProvider(): array
    {
        return [
            [Color::RESET, Color::RESET],
            [Color::WHITE, Color::WHITE],
        ];
    }

    public function testWithColorsReturnSameInstance(): void
    {
        self::assertSame($this->config, $this->config->colors());
    }

    /**
     * @return array<array{0:string}>
     */
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
        $this->config->bodyCharacter($input);
    }
    /**
     * @dataProvider providerGaps
     */
    public function testLeftMargin(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->leftMarginSize($gap)->leftMarginSize);
    }

    /**
     * @dataProvider providerGaps
     */
    public function testGap(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->gapSize($gap)->gapSize);
    }

    /**
     * @return iterable<string, array{gap:int, expected:int}>
     */
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
    public function testPadding(Alignment $padding, Alignment $expected): void
    {
        self::assertSame($expected, $this->config->labelAlignment($padding)->labelAlignment);
    }

    public function testAlignmentWillFail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Alignment::fromPadding(42);
    }

    /**
     * @return iterable<string, array{padding:Alignment, expected:Alignment}>
     */
    public function providerPaddings(): iterable
    {
        return [
            'default' => [
                'padding' => Alignment::LEFT,
                'expected' => Alignment::LEFT,
            ],
            'changing wit a defined config' => [
                'padding' => Alignment::RIGHT,
                'expected' => Alignment::RIGHT,
            ],
        ];
    }

    public function testWithOutputAlwaysReturnsANewInstance(): void
    {
        $newConfig = $this->config->output(new StreamOutput(STDOUT));
        self::assertNotSame($this->config, $newConfig);
        self::assertEquals($newConfig->output, $this->config->output);
    }

    public function testConstructors(): void
    {
        self::assertEquals(
            GanttChartConfig::fromOutput(new StreamOutput(STDERR)),
            GanttChartConfig::fromStream(STDERR)
        );
    }
}
