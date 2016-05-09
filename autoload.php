<?php

spl_autoload_register(function ($className) {

    $prefix = 'League\Period\\';
    if (0 !== strpos($className, $prefix)) {
        return;
    }

    $file = __DIR__
        .DIRECTORY_SEPARATOR
        .'src'
        .DIRECTORY_SEPARATOR
        .str_replace('\\', DIRECTORY_SEPARATOR, substr($className, strlen($prefix)))
        .'.php';
    if (!is_readable($file)) {
        return;
    }

    require $file;
});
