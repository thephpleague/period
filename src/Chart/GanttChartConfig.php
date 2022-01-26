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

use function array_filter;
use function array_map;
use function mb_convert_encoding;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use const STDOUT;
use const STR_PAD_BOTH;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * A class to configure the console output settings.
 */
final class GanttChartConfig
{
    private const REGEXP_UNICODE = '/\\\\u(?<unicode>[0-9A-F]{1,4})/i';

    public const ALIGN_LEFT = STR_PAD_RIGHT;
    public const ALIGN_RIGHT = STR_PAD_LEFT;
    public const ALIGN_CENTER = STR_PAD_BOTH;

    public static function create(Output $output = new ConsoleOutput(STDOUT)): self
    {
        return new self($output);
    }

    private function __construct(
        public readonly Output $output,
        public readonly int $width = 60,
        public readonly string $endExcludedCharacter = ')',
        public readonly string $endIncludedCharacter = ']',
        public readonly string $startExcludedCharacter = '(',
        public readonly string $startIncludedCharacter = '[',
        public readonly string $body = '-',
        public readonly string $space = ' ',
        public readonly int $leftMarginSize = 1,
        public readonly int $gapSize = 1,
        public readonly int $labelAlignment = self::ALIGN_LEFT,
        /** @var array<string> */
        public readonly array $colors = [Output::COLOR_DEFAULT],
    ) {
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     */
    public static function fromRandom(Output $output = new ConsoleOutput(STDOUT)): self
    {
        return self::create($output)->withColors(Output::COLORS[array_rand(Output::COLORS)]);
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     */
    public static function fromRainbow(Output $output = new ConsoleOutput(STDOUT)): self
    {
        return self::create($output)->withColors(...Output::COLORS);
    }

    /**
     * Return an instance with the start excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start excluded character.
     */
    public function withStartExcludedCharacter(string $startExcludedCharacter): self
    {
        $startExcludedCharacter = $this->filterPattern($startExcludedCharacter, 'startExcludedCharacter');
        if ($startExcludedCharacter === $this->startExcludedCharacter) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
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
        if (1 === mb_strlen($result)) {
            return $result;
        }

        throw UnableToDrawChart::dueToInvalidUnicodeChar($str);
    }

    /**
     * Return an instance with a new output object.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified output class.
     */
    public function withOutput(Output $output): self
    {
        return new self(
            $output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the start included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start included character.
     */
    public function withStartIncludedCharacter(string $startIncludedCharacter): self
    {
        $startIncludedCharacter = $this->filterPattern($startIncludedCharacter, 'startIncludedCharacter');
        if ($startIncludedCharacter === $this->startIncludedCharacter) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the end excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end excluded character.
     */
    public function withEndExcludedCharacter(string $endExcludedCharacter): self
    {
        $endExcludedCharacter = $this->filterPattern($endExcludedCharacter, 'endExcludedCharacter');
        if ($endExcludedCharacter === $this->endExcludedCharacter) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the end included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end included character.
     */
    public function withEndIncludedCharacter(string $endIncludedCharacter): self
    {
        $endIncludedCharacter = $this->filterPattern($endIncludedCharacter, 'endIncludedCharacter');
        if ($endIncludedCharacter === $this->endIncludedCharacter) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the specified row width.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified width.
     */
    public function withWidth(int $width): self
    {
        if ($width < 10) {
            $width = 10;
        }

        if ($width === $this->width) {
            return $this;
        }

        return new self(
            $this->output,
            $width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the specified body block.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified body pattern.
     */
    public function withBody(string $body): self
    {
        $body = $this->filterPattern($body, 'body');
        if ($body === $this->body) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with the space pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified space character.
     */
    public function withSpace(string $space): self
    {
        $space = $this->filterPattern($space, 'space');
        if ($space === $this->space) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with a new color palette.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified color palette.
     */
    public function withColors(string ...$colors): self
    {
        $colors = array_filter(
            array_map('strtolower', $colors),
            fn (string $value): bool => in_array($value, Output::COLORS, true)
        );

        if ([] === $colors) {
            $colors = [Output::COLOR_DEFAULT];
        }

        if ($colors === $this->colors) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $colors,
        );
    }

    /**
     * Return an instance with a new left margin size.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified left margin size.
     */
    public function withLeftMarginSize(int $leftMarginSize): self
    {
        if ($leftMarginSize === $this->leftMarginSize) {
            return $this;
        }

        if ($leftMarginSize < 0) {
            $leftMarginSize = 1;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $leftMarginSize,
            $this->gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }
    /**
     * Return an instance with a new gap size.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified gap size.
     */
    public function withGapSize(int $gapSize): self
    {
        if ($gapSize === $this->gapSize) {
            return $this;
        }

        if ($gapSize < 0) {
            $gapSize = 1;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $gapSize,
            $this->labelAlignment,
            $this->colors,
        );
    }

    /**
     * Return an instance with a left padding.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that set a left padding to the line label.
     */
    public function withLabelAlignment(int $labelAlignment): self
    {
        if (!in_array($labelAlignment, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
            $labelAlignment = STR_PAD_RIGHT;
        }

        if ($this->labelAlignment === $labelAlignment) {
            return $this;
        }

        return new self(
            $this->output,
            $this->width,
            $this->endExcludedCharacter,
            $this->endIncludedCharacter,
            $this->startExcludedCharacter,
            $this->startIncludedCharacter,
            $this->body,
            $this->space,
            $this->leftMarginSize,
            $this->gapSize,
            $labelAlignment,
            $this->colors,
        );
    }
}
