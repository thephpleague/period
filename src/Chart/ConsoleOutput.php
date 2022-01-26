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
use function strtolower;
use const PHP_EOL;
use const PHP_OS;

final class ConsoleOutput implements Output
{
    private const REGEXP_POSIX_PLACEHOLDER = '/(\s+)/msi';

    /** @var resource */
    private $stream;

    /**
     * @param resource|mixed $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new TypeError('Argument passed must be a stream resource.');
        }

        $this->stream = $resource;
    }

    public function writeln(string $message = '', Color $color = Color::NONE): void
    {
        fwrite($this->stream, $this->format($this->colorize($message, $color)).PHP_EOL);
        fflush($this->stream);
    }

    /**
     * Colorizes the given string.
     */
    private function colorize(string $characters, Color $color): string
    {
        if (Color::NONE === $color) {
            return $characters;
        }

        return "<<$color->value>>$characters<<".Color::RESET->value.'>>';
    }

    /**
     * Returns a formatted line.
     */
    private function format(string $str): string
    {
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
        if ($formatter instanceof Closure) {
            return $formatter;
        }

        if (str_starts_with(strtolower(PHP_OS), 'win')) {
            $formatter = fn (array $matches): string => (string)$matches[0];

            return $formatter;
        }

        $formatter = fn (array $matches): string => chr(27).'['.strtr(
            (string) preg_replace(self::REGEXP_POSIX_PLACEHOLDER, ';', (string) $matches[1]),
            array_reduce(Color::cases(), fn (array $carry, Color $color): array => [...$carry, ...[$color->value => $color->code()]], [])
        ).'m';

        return $formatter;
    }
}
