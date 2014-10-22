Period
============

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Latest Version](https://img.shields.io/github/release/thephpleague/period.svg?style=flat-square)](https://github.com/thephpleague/period/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br>
[![Build Status](https://img.shields.io/travis/thephpleague/period/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/period)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/period.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/period/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/period.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/period)
[![Total Downloads](https://img.shields.io/packagist/dt/league/period.svg?style=flat-square)](https://packagist.org/packages/league/period)

This class is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes and helps resolve many recurrent issues around Date range selection and usage.

This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/

System Requirements
-------

You need **PHP >= 5.3.0** to use `Period` but the latest stable version of PHP is recommended.

Install
-------

Install `Period` using Composer.

```
$ composer require league/period
```

This will edit (or create) your `composer.json` file and automatically choose the most recent version, for example: `~1.0`

#### Going Solo

You can also use `League\Period` without using Composer by downloading the library and using a [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

Documentation
-------

`League\Period` is [fully documented](http://period.thephpleague.com). Contribute to this documentation in the [gh-pages branch](https://github.com/thephpleague/period/tree/gh-pages).

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/period/graphs/contributors)
