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

spl_autoload_register(function (string $className): void {

    static $length = 14; //strlen($prefix)
    $prefix = 'League\Period\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }

    $file = __DIR__.'/src/'.str_replace('\\', '/', substr($className, $length)).'.php';
    if (is_readable($file)) {
        require $file;
    }
});
