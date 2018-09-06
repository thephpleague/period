<?php

require __DIR__.'/src/functions_include.php';

spl_autoload_register(function ($className) {

    $prefix = 'League\Period\\';
    if (0 !== strpos($className, $prefix)) {
        return;
    }

    $file = __DIR__.'/src/'.str_replace('\\', '/', substr($className, strlen($prefix))).'.php';
    if (!is_readable($file)) {
        return;
    }

    require $file;
});
