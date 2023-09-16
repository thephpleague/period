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

use function mb_convert_encoding;
use function mb_strlen;
use function preg_match;
use function preg_replace;

use const STDOUT;

/**
 * A class to configure the settings to stroke data using the GanttChart.
 *
 * @see GanttChart
 */
final class GanttChartConfig
{
    private const REGEXP_UNICODE = '/\\\\u(?<unicode>[0-9A-F]{1,4})/i';

    public function __construct(
        public readonly Output $output = new StreamOutput(STDOUT, Terminal::Posix),
        /** @var array<Color> */
        public readonly array $colors = [Color::Reset],
        public readonly string $startExcludedCharacter = '(',
        public readonly string $startIncludedCharacter = '[',
        public readonly string $endExcludedCharacter = ')',
        public readonly string $endIncludedCharacter = ']',
        public readonly string $bodyCharacter = '-',
        public readonly string $spaceCharacter = ' ',
        public readonly int $width = 60,
        public readonly int $gapSize = 1,
        public readonly int $leftMarginSize = 1,
        public readonly Alignment $labelAlignment = Alignment::Left,
    ) {
    }

    /**
     * Returns a CLI Renderer to Display the graph.
     *
     * @param resource $stream
     */
    public static function fromStream($stream, Terminal $terminal = Terminal::Posix): self
    {
        return new self(new StreamOutput($stream, $terminal));
    }

    /**
     * Returns a basic CLI Renderer to Display the graph.
     */
    public static function fromOutput(Output $output): self
    {
        return new self($output);
    }

    /**
     * Returns a CLI Renderer to Display the graph with a random color.
     */
    public static function fromRandomColor(): self
    {
        $cases = Color::rainBow();

        return new self(colors: [$cases[array_rand($cases)]]);
    }

    /**
     * Returns a CLI Renderer to Display the graph using the POSIX Rainbow.
     */
    public static function fromRainbow(): self
    {
        return new self(colors: Color::rainBow());
    }

    /**
     * Filter the submitted string.
     *
     * @throws UnableToDrawChart if the pattern is invalid
     */
    private function filterPattern(string $str, string $part): string
    {
        return match (true) {
            1 === mb_strlen($str) => $str,
            1 === preg_match(self::REGEXP_UNICODE, $str) => $this->filterUnicodeCharacter($str),
            default => throw UnableToDrawChart::dueToInvalidPattern($part),
        };
    }

    /**
     * Decode unicode characters.
     *
     * @see http://stackoverflow.com/a/37415135/2316257
     *
     * @throws UnableToDrawChart if the character is not valid.
     */
    private function filterUnicodeCharacter(string $str): string
    {
        $replaced = (string) preg_replace(self::REGEXP_UNICODE, '&#x$1;', $str);
        $result = mb_convert_encoding($replaced, 'UTF-16', 'HTML-ENTITIES');
        $result = mb_convert_encoding($result, 'UTF-8', 'UTF-16');

        return match (1) {
            mb_strlen($result) => $result,
            default => throw UnableToDrawChart::dueToInvalidUnicodeChar($str),
        };
    }

    /**
     * Returns an instance with the start excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start excluded character.
     */
    public function startExcludedCharacter(string $startExcludedCharacter): self
    {
        $startExcludedCharacter = $this->filterPattern($startExcludedCharacter, 'startExcludedCharacter');

        return match ($this->startExcludedCharacter) {
            $startExcludedCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with a new output object.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified output class.
     */
    public function output(Output $output): self
    {
        return new self(
            $output,
            $this->colors,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->bodyCharacter,
            $this->spaceCharacter,
            $this->width,
            $this->gapSize,
            $this->leftMarginSize,
            $this->labelAlignment,
        );
    }

    /**
     * Return an instance with the start included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start included character.
     */
    public function startIncludedCharacter(string $startIncludedCharacter): self
    {
        $startIncludedCharacter = $this->filterPattern($startIncludedCharacter, 'startIncludedCharacter');

        return match ($this->startIncludedCharacter) {
            $startIncludedCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with the end excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end excluded character.
     */
    public function endExcludedCharacter(string $endExcludedCharacter): self
    {
        $endExcludedCharacter = $this->filterPattern($endExcludedCharacter, 'endExcludedCharacter');

        return match ($this->endExcludedCharacter) {
            $endExcludedCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with the end included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end included character.
     */
    public function endIncludedCharacter(string $endIncludedCharacter): self
    {
        $endIncludedCharacter = $this->filterPattern($endIncludedCharacter, 'endIncludedCharacter');

        return match ($this->endIncludedCharacter) {
            $endIncludedCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with the specified row width.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified width.
     */
    public function width(int $width): self
    {
        if ($width < 10) {
            $width = 10;
        }

        return match ($this->width) {
            $width => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with the specified body block.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified body pattern.
     */
    public function bodyCharacter(string $bodyCharacter): self
    {
        $bodyCharacter = $this->filterPattern($bodyCharacter, 'body');

        return match ($this->bodyCharacter) {
            $bodyCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with the space pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified space character.
     */
    public function spaceCharacter(string $spaceCharacter): self
    {
        $spaceCharacter = $this->filterPattern($spaceCharacter, 'spaceCharacter');

        return match ($this->spaceCharacter) {
            $spaceCharacter => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with a new color palette.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified color palette.
     */
    public function colors(Color ...$colors): self
    {
        if ([] === $colors) {
            $colors = [Color::Reset];
        }

        return match ($this->colors) {
            $colors => $this,
            default => new self(
                $this->output,
                $colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with a new left margin size.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified left margin size.
     */
    public function leftMarginSize(int $leftMarginSize): self
    {
        if ($leftMarginSize < 0) {
            $leftMarginSize = 1;
        }

        return match ($this->leftMarginSize) {
            $leftMarginSize => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }
    /**
     * Return an instance with a new gap size.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified gap size.
     */
    public function gapSize(int $gapSize): self
    {
        if ($gapSize < 0) {
            $gapSize = 1;
        }

        return match ($this->gapSize) {
            $gapSize => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $gapSize,
                $this->leftMarginSize,
                $this->labelAlignment,
            ),
        };
    }

    /**
     * Return an instance with a left padding.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that set a left padding to the line label.
     */
    public function labelAlignment(Alignment $labelAlignment): self
    {
        return match ($this->labelAlignment) {
            $labelAlignment => $this,
            default => new self(
                $this->output,
                $this->colors,
                $this->startExcludedCharacter,
                $this->startIncludedCharacter,
                $this->endExcludedCharacter,
                $this->endIncludedCharacter,
                $this->bodyCharacter,
                $this->spaceCharacter,
                $this->width,
                $this->gapSize,
                $this->leftMarginSize,
                $labelAlignment,
            ),
        };
    }
}
