Period
============

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Latest Version](https://img.shields.io/github/release/thephpleague/period.svg?style=flat-square)](https://github.com/thephpleague/period/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/period/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/period)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/period.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/period/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/league/period.svg?style=flat-square)](https://packagist.org/packages/league/period)

`Period` is PHP's missing time range API. It is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes and extends the concept to cover all basic operations regarding time ranges.

## Highlights

- Treats a time range as an immutable value object
- Exposes many named constructors to ease time range creation
- Covers all basic manipulations related to time range
- Fully documented
- Framework-agnostic
- Composer ready, [PSR-2], and [PSR-4] compliant

Documentation
-------

Full documentation can be found at [period.thephpleague.com](http://period.thephpleague.com).

System Requirements
-------

You need **PHP >= 7.2.0** but the latest stable version of PHP is recommended.

Install
-------

Install `Period` using Composer.

```
$ composer require league/period
```

Testing
-------

`Period` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [Ignace Nyamagana Butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/period/graphs/contributors)

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
