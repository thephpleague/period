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
use function sprintf;
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

    /**
     * @var Output
     */
    private $output;

    /**
     * @var string[]
     */
    private $colors = [Output::COLOR_DEFAULT];

    /**
     * @var int
     */
    private $width = 60;

    /**
     * @var string
     */
    private $endExcludedChar = ')';

    /**
     * @var string
     */
    private $endIncludedChar = ']';

    /**
     * @var string
     */
    private $startExcludedChar = '(';

    /**
     * @var string
     */
    private $startIncludedChar = '[';

    /**
     * @var string
     */
    private $body = '-';

    /**
     * @var string
     */
    private $space = ' ';

    /**
     * @var int
     */
    private $leftMarginSize = 1;

    /**
     * @var int
     */
    private $gapSize = 1;

    /**
     * @var int
     */
    private $alignLabel = self::ALIGN_LEFT;

    /**
     * New instance.
     *
     * @param ?Output $output
     */
    public function __construct(?Output $output = null)
    {
        $this->output = $output ?? new ConsoleOutput(STDOUT);
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     *
     * @param ?Output $output
     */
    public static function createFromRandom(?Output $output = null): self
    {
        $index = array_rand(Output::COLORS);

        $config = new self($output);
        $config->colors = [Output::COLORS[$index]];

        return $config;
    }

    /**
     * Create a Cli Renderer to Display the millipede in Rainbow.
     *
     * @param ?Output $output
     */
    public static function createFromRainbow(?Output $output = null): self
    {
        $config = new self($output);
        $config->colors = Output::COLORS;

        return $config;
    }

    /**
     * Returns the Output class.
     */
    public function output(): Output
    {
        return $this->output;
    }

    /**
     * Retrieves the start excluded block character.
     */
    public function startExcluded(): string
    {
        return $this->startExcludedChar;
    }
    /**
     * Retrieves the start included block character.
     */
    public function startIncluded(): string
    {
        return $this->startIncludedChar;
    }

    /**
     * Retrieves the excluded end block character.
     */
    public function endExcluded(): string
    {
        return $this->endExcludedChar;
    }

    /**
     * Retrieves the excluded end block character.
     */
    public function endIncluded(): string
    {
        return $this->endIncludedChar;
    }

    /**
     * Retrieves the row width.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Retrieves the body block character.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Retrieves the row space character.
     */
    public function space(): string
    {
        return $this->space;
    }

    /**
     * The selected colors for each rows.
     *
     * @return string[]
     */
    public function colors(): array
    {
        return $this->colors;
    }

    /**
     * Retrieves the left margin size before the label name.
     */
    public function leftMarginSize(): int
    {
        return $this->leftMarginSize;
    }

    /**
     * Retrieves the gap size between the label and the line.
     */
    public function gapSize(): int
    {
        return $this->gapSize;
    }

    /**
     * Returns how label should be aligned.
     */
    public function labelAlign(): int
    {
        return $this->alignLabel;
    }

    /**
     * Return an instance with the start excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start excluded character.
     */
    public function withStartExcluded(string $startExcludedChar): self
    {
        $startExcludedChar = $this->filterPattern($startExcludedChar, 'startExcluded');
        if ($startExcludedChar === $this->startExcludedChar) {
            return $this;
        }

        $clone = clone $this;
        $clone->startExcludedChar = $startExcludedChar;

        return $clone;
    }

    /**
     * Filter the submitted string.
     *
     * @throws \InvalidArgumentException if the pattern is invalid
     */
    private function filterPattern(string $str, string $part): string
    {
        if (1 === mb_strlen($str)) {
            return $str;
        }

        if (1 === preg_match(self::REGEXP_UNICODE, $str)) {
            return $this->filterUnicodeCharacter($str);
        }

        throw new \InvalidArgumentException(sprintf('The %s pattern must be a single character', $part));
    }

    /**
     * Decode unicode characters.
     *
     * @see http://stackoverflow.com/a/37415135/2316257
     *
     * @throws \InvalidArgumentException if the character is not valid.
     */
    private function filterUnicodeCharacter(string $str): string
    {
        $replaced = (string) preg_replace(self::REGEXP_UNICODE, '&#x$1;', $str);
        $result = mb_convert_encoding($replaced, 'UTF-16', 'HTML-ENTITIES');
        $result = mb_convert_encoding($result, 'UTF-8', 'UTF-16');
        if (1 === mb_strlen($result)) {
            return $result;
        }

        throw new \InvalidArgumentException(sprintf('The given string `%s` is not a valid unicode string', $str));
    }

    /**
     * Return an instance with a new output object.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified output class.
     */
    public function withOutput(Output $output): self
    {
        $clone = clone $this;
        $clone->output = $output;

        return $clone;
    }

    /**
     * Return an instance with the start included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified start included character.
     */
    public function withStartIncluded(string $startIncludedChar): self
    {
        $startIncludedChar = $this->filterPattern($startIncludedChar, 'startIncluded');
        if ($startIncludedChar === $this->startIncludedChar) {
            return $this;
        }

        $clone = clone $this;
        $clone->startIncludedChar = $startIncludedChar;

        return $clone;
    }

    /**
     * Return an instance with the end excluded pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end excluded character.
     */
    public function withEndExcluded(string $endExcludedChar): self
    {
        $endExcludedChar = $this->filterPattern($endExcludedChar, 'endExcluded');
        if ($endExcludedChar === $this->endExcludedChar) {
            return $this;
        }

        $clone = clone $this;
        $clone->endExcludedChar = $endExcludedChar;

        return $clone;
    }

    /**
     * Return an instance with the end included pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified end included character.
     */
    public function withEndIncluded(string $endIncludedChar): self
    {
        $endIncludedChar = $this->filterPattern($endIncludedChar, 'endIncluded');
        if ($endIncludedChar === $this->endIncludedChar) {
            return $this;
        }

        $clone = clone $this;
        $clone->endIncludedChar = $endIncludedChar;

        return $clone;
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

        $clone = clone $this;
        $clone->width = $width;

        return $clone;
    }

    /**
     * Return an instance with the specified body block.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified body pattern.
     */
    public function withBody(string $bodyChar): self
    {
        $bodyChar = $this->filterPattern($bodyChar, 'body');
        if ($bodyChar === $this->body) {
            return $this;
        }

        $clone = clone $this;
        $clone->body = $bodyChar;

        return $clone;
    }

    /**
     * Return an instance with the space pattern.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified space character.
     */
    public function withSpace(string $spaceChar): self
    {
        $spaceChar = $this->filterPattern($spaceChar, 'space');
        if ($spaceChar === $this->space) {
            return $this;
        }

        $clone = clone $this;
        $clone->space = $spaceChar;

        return $clone;
    }

    /**
     * Return an instance with a new color palette.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified color palette.
     *
     * @param string... $colors
     */
    public function withColors(string ...$colors): self
    {
        $filter = static function ($value): bool {
            return in_array($value, Output::COLORS, true);
        };

        $colors = array_filter(array_map('strtolower', $colors), $filter);
        if ([] === $colors) {
            $colors = [Output::COLOR_DEFAULT];
        }

        if ($colors === $this->colors) {
            return $this;
        }

        $clone = clone $this;
        $clone->colors = $colors;

        return $clone;
    }

    /**
     * Return an instance with a new left margin size.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified left margin size.
     */
    public function withLeftMarginSize(int $leftMarginSize): self
    {
        if ($leftMarginSize  === $this->leftMarginSize) {
            return $this;
        }

        if ($leftMarginSize < 0) {
            $leftMarginSize = 1;
        }

        $clone = clone $this;
        $clone->leftMarginSize = $leftMarginSize;

        return $clone;
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

        $clone = clone $this;
        $clone->gapSize = $gapSize;

        return $clone;
    }

    /**
     * Return an instance with a left padding.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that set a left padding to the line label.
     */
    public function withLabelAlign(int $align): self
    {
        if (!in_array($align, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
            $align = STR_PAD_RIGHT;
        }

        if ($this->alignLabel === $align) {
            return $this;
        }

        $clone = clone $this;
        $clone->alignLabel = $align;

        return $clone;
    }
}
