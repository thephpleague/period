---
layout: default
title: Installation
redirect_from: /installation/
---

# Installation

## System Requirements

`Period` requires **PHP 5.5.9** but the latest stable version of PHP or HHVM is recommended.

## Composer

`Period` is available on [Packagist](https://packagist.org/packages/league/period) and can be installed using [Composer](https://getcomposer.org/).

~~~bash
$ composer require league/period
~~~

## Going Solo

You can also use `Period` without using Composer by downloading the library and using any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

Starting with version 3.2. `Period` comes bundle with its own autoloader you can call as shown below:

~~~php
<?php

require 'path/to/league/repo/autoload.php';

var_dump(Period::createFromDay('TODAY'));
~~~

where `path/to/league/repo` represents the path where the library was extracted.
