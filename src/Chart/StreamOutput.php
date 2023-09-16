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

use Closure;
use TypeError;

use function chr;
use function fflush;
use function fwrite;
use function implode;
use function preg_replace;
use function preg_replace_callback;

use const PHP_EOL;

final class StreamOutput implements Output
{
    private const REGEXP_POSIX_PLACEHOLDER = '/(\s+)/msi';

    /** @var resource */
    private $stream;

    /**
     * @param mixed $stream stream resource
     */
    public function __construct(mixed $stream, public readonly Terminal $terminal)
    {
        if (!is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new TypeError('Argument passed must be a stream resource.');
        }

        $this->stream = $stream;
    }

    public function writeln(string $message, Color $color = Color::None): void
    {
        fwrite($this->stream, $this->format($this->colorize($message, $color)).PHP_EOL);
        fflush($this->stream);
    }

    /**
     * Colorizes the given string.
     */
    private function colorize(string $characters, Color $color): string
    {
        return match (true) {
            Color::None === $color,
            Terminal::Posix !== $this->terminal => $characters,
            default => "<<$color->value>>$characters<<".Color::Reset->value.'>>',
        };
    }

    /**
     * Returns a formatted line.
     */
    private function format(string $str): string
    {
        if (Terminal::Posix !== $this->terminal) {
            return $str;
        }

        /** @var string|null $regexp */
        static $regexp;
        if (null === $regexp) {
            $regexp = ',<<\s*((('.implode('|', array_map(fn (Color $c): string => $c->value, Color::cases())).')(\s*))+)>>,Umsi';
        }

        return (string) preg_replace_callback($regexp, $this->formatter(), $str);
    }

    /**
     * Return a writer formatter depending on the OS.
     */
    private function formatter(): Closure
    {
        static $formatter;
        if (!$formatter instanceof Closure) {
            $formatter = fn (array $matches): string => chr(27).'['.strtr(
                (string) preg_replace(self::REGEXP_POSIX_PLACEHOLDER, ';', (string) $matches[1]),
                array_reduce(Color::cases(), fn (array $carry, Color $color): array => [...$carry, ...[$color->value => $color->posix()]], [])
            ).'m';
        }

        return $formatter;
    }
}
