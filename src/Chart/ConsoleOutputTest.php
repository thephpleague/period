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

use PHPUnit\Framework\TestCase;
use TypeError;
use function chr;
use function curl_init;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \League\Period\Chart\ConsoleOutput
 */
final class ConsoleOutputTest extends TestCase
{
    /**
     * @return resource
     */
    private function setStream()
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');

        return $stream;
    }

    public function testCreateStreamWithInvalidParameter(): void
    {
        $this->expectException(TypeError::class);
        new ConsoleOutput(__DIR__.'/data/foo.csv');
    }

    public function testCreateStreamWithWrongResourceType(): void
    {
        $this->expectException(TypeError::class);
        new ConsoleOutput(curl_init());
    }

    /**
     * @dataProvider provideWritelnTexts
     */
    public function testWriteln(string $message, string $expected): void
    {
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream);
        $output->writeln($message, 'blue');
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($expected, $data);
    }

    public function provideWritelnTexts(): iterable
    {
        return [
            'empty message' => [
                'message' => '',
                'expected' => '',
            ],
            'simple message' => [
                'message' => "I'm the king of the world",
                'expected' => chr(27).'[34m'."I'm the king of the world".chr(27).'[0m'.PHP_EOL,
            ],
        ];
    }

    public function testWritelnWithUnknownColor(): void
    {
        $message = 'foobar the quick brown fox';
        $stream = $this->setStream();
        $output = new ConsoleOutput($stream);
        $output->writeln($message, 'pink');
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($message.PHP_EOL, $data);
    }
}
