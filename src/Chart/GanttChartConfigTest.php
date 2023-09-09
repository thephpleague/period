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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const STDOUT;

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
        self::assertSame([Color::Reset], $this->config->colors);
        self::assertSame(Alignment::Left, $this->config->labelAlignment);
    }

    public function testCreateFromRandom(): void
    {
        $config1 = GanttChartConfig::fromRandomColor();
        $config2 = GanttChartConfig::fromRainbow();
        self::assertContains($config1->colors[0], $config2->colors);
    }

    #[DataProvider('widthProvider')]
    public function testWidth(int $size, int $expected): void
    {
        self::assertSame($expected, $this->config->width($size)->width);
    }

    /**
     * @return array<string, array{0:int, 1:int}>
     */
    public static function widthProvider(): array
    {
        return [
            '0 size' => [0, 10],
            'negative size' => [-23, 10],
            'basic usage' => [23, 23],
            'default value' => [60, 60],
        ];
    }

    #[DataProvider('providerChars')]
    public function testBody(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->bodyCharacter($char)->bodyCharacter);
    }

    #[DataProvider('providerChars')]
    public function testEndExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->endExcludedCharacter($char)->endExcludedCharacter);
    }

    #[DataProvider('providerChars')]
    public function testEndIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->endIncludedCharacter($char)->endIncludedCharacter);
    }

    #[DataProvider('providerChars')]
    public function testStartExcluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->startExcludedCharacter($char)->startExcludedCharacter);
    }

    #[DataProvider('providerChars')]
    public function testStartIncluded(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->startIncludedCharacter($char)->startIncludedCharacter);
    }

    #[DataProvider('providerChars')]
    public function testSpace(string $char, string $expected): void
    {
        self::assertSame($expected, $this->config->spaceCharacter($char)->spaceCharacter);
    }

    /**
     * @return array<array{0:string, 1:string}>
     */
    public static function providerChars(): array
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

    #[DataProvider('colorsProvider')]
    public function testColors(Color $char, Color $expected): void
    {
        self::assertSame($expected, $this->config->colors($char)->colors[0]);
    }

    /**
     * @return array<array{0:Color, 1:Color}>
     */
    public static function colorsProvider(): array
    {
        return [
            [Color::Reset, Color::Reset],
            [Color::White, Color::White],
        ];
    }

    public function testWithColorsReturnSameInstance(): void
    {
        self::assertSame($this->config, $this->config->colors());
    }

    /**
     * @return array<array{0:string}>
     */
    public static function providerInvalidChars(): array
    {
        return [
            ['coucou'],
            ['\uD83D\uDE00\uD83D\uDE00'],
        ];
    }

    #[DataProvider('providerInvalidChars')]
    public function testWithHeadBlockThrowsInvalidArgumentException(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->config->bodyCharacter($input);
    }

    #[DataProvider('providerGaps')]
    public function testLeftMargin(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->leftMarginSize($gap)->leftMarginSize);
    }

    #[DataProvider('providerGaps')]
    public function testGap(int $gap, int $expected): void
    {
        self::assertSame($expected, $this->config->gapSize($gap)->gapSize);
    }

    /**
     * @return iterable<string, array{gap:int, expected:int}>
     */
    public static function providerGaps(): iterable
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

    #[DataProvider('providerPaddings')]
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
    public static function providerPaddings(): iterable
    {
        return [
            'default' => [
                'padding' => Alignment::Left,
                'expected' => Alignment::Left,
            ],
            'changing wit a defined config' => [
                'padding' => Alignment::Right,
                'expected' => Alignment::Right,
            ],
        ];
    }

    public function testWithOutputAlwaysReturnsANewInstance(): void
    {
        $newConfig = $this->config->output(new StreamOutput(STDOUT, Terminal::Posix));
        self::assertNotSame($this->config, $newConfig);
        self::assertEquals($newConfig->output, $this->config->output);
    }

    public function testConstructors(): void
    {
        self::assertEquals(
            GanttChartConfig::fromOutput(new StreamOutput(STDERR, Terminal::Posix)),
            GanttChartConfig::fromStream(STDERR)
        );
    }
}
